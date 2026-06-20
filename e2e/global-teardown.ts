import { existsSync, unlinkSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const DB_PATH = path.join(__dirname, '..', 'database', 'e2e.sqlite');

/**
 * Removes the E2E SQLite database file after all tests finish.
 * Keeps the workspace clean.
 */
export default async function globalTeardown() {
    if (existsSync(DB_PATH)) {
        unlinkSync(DB_PATH);
        console.log(`[e2e teardown] removed ${DB_PATH}`);
    }
}
