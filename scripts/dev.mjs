import { spawn, spawnSync } from 'node:child_process';

const commands = [
    { command: 'php artisan serve' },
    { command: 'php artisan queue:listen --tries=1 --timeout=0' },
    { command: 'npm run dev' },
];

const pcntlCheck = spawnSync('php', ['-r', "exit(function_exists('pcntl_fork') ? 0 : 1);"], {
    stdio: 'ignore',
});

if (pcntlCheck.status === 0) {
    commands.splice(2, 0, { command: 'php artisan pail --timeout=0' });
} else {
    console.warn('[dev] Skipping php artisan pail because the current PHP runtime does not provide pcntl.');
}

const children = [];
let stopping = false;

function stopAll(exitCode = 1) {
    if (stopping) {
        return;
    }

    stopping = true;

    for (const child of children) {
        if (!child.killed) {
            child.kill('SIGTERM');
        }
    }

    process.exit(exitCode);
}

function start(command) {
    const child = spawn(command, {
        shell: true,
        stdio: 'inherit',
        env: process.env,
    });

    children.push(child);

    child.on('exit', (code, signal) => {
        if (stopping) {
            return;
        }

        if (signal) {
            stopAll(1);
            return;
        }

        stopAll(code ?? 1);
    });

    return child;
}

for (const { command } of commands) {
    start(command);
}

process.on('SIGINT', () => stopAll(0));
process.on('SIGTERM', () => stopAll(0));
