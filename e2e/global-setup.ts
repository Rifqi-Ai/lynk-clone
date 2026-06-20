import { execSync } from 'node:child_process';
import { existsSync, unlinkSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const DB_PATH = path.join(__dirname, '..', 'database', 'e2e.sqlite');

/**
 * Wipes the E2E SQLite database, runs migrations, and seeds demo data.
 * Runs once before all tests start.
 */
export default async function globalSetup() {
    // Remove any stale database from a previous run.
    if (existsSync(DB_PATH)) {
        unlinkSync(DB_PATH);
    }

    const env = {
        ...process.env,
        APP_ENV: 'testing',
        DB_CONNECTION: 'sqlite',
        DB_DATABASE: DB_PATH,
    };

    const run = (cmd: string) => {
        console.log(`[e2e setup] ${cmd}`);
        execSync(cmd, { stdio: 'inherit', env: env as NodeJS.ProcessEnv });
    };

    run('php artisan migrate:fresh --force --no-interaction');
    run('php artisan db:seed --class=DemoSeeder --force --no-interaction');
}
