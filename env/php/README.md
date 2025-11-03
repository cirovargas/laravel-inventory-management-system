# PHP Docker Configuration

This directory contains the Docker configuration for the PHP container with cron and supervisord support.

## Components

### 1. Dockerfile
The main Dockerfile that builds the PHP container with:
- PHP 8.4 CLI (Alpine-based)
- Cron (dcron) for scheduled tasks
- Supervisord for process management
- All required PHP extensions

### 2. supervisord.conf
Main supervisord configuration file that:
- Runs supervisord in foreground mode (`nodaemon=true`)
- Configures logging to `/var/log/supervisor/`
- Includes additional program configurations from `/etc/supervisor/conf.d/`

### 3. supervisor.d/ Directory
Contains individual program configurations:

#### octane.conf
- Manages Laravel Octane server with Swoole
- Runs on port 3000
- Configured with 4 workers and 6 task workers
- Auto-restarts on failure

#### queue-worker.conf
- Manages Laravel queue workers
- Runs 2 worker processes
- Auto-restarts on failure
- Max execution time: 3600 seconds (1 hour)
- Max tries: 3

### 4. Cron Configuration
- Laravel scheduler runs every minute
- Executes `php artisan schedule:run`
- Configured in `/etc/crontabs/root`

## Process Management

The container uses a custom entrypoint script that:
1. Runs database migrations
2. Starts cron daemon in background
3. Starts supervisord in foreground (keeps container alive)

Supervisord manages:
- Laravel Octane server
- Queue workers (2 processes)

## Customization

### Adding New Supervised Programs
Create a new `.conf` file in `supervisor.d/` directory:

```ini
[program:my-program]
process_name=%(program_name)s
command=php artisan my:command
directory=/app
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=root
numprocs=1
redirect_stderr=true
stdout_logfile=/var/log/supervisor/my-program.log
```

### Modifying Queue Workers
Edit `supervisor.d/queue-worker.conf`:
- Change `numprocs` to adjust number of workers
- Modify `--sleep`, `--tries`, or `--max-time` parameters
- Add `--queue=` to specify queue names

### Modifying Cron Schedule
The cron schedule is set in the Dockerfile. To change it, modify line 53:
```dockerfile
RUN echo "* * * * * cd /app && php artisan schedule:run >> /dev/null 2>&1" > /etc/crontabs/root
```

## Logs

All supervisor logs are stored in `/var/log/supervisor/`:
- `supervisord.log` - Main supervisord log
- `octane.log` - Laravel Octane output
- `queue-worker.log` - Queue worker output

## Monitoring

To check process status inside the container:
```bash
docker exec <container-name> supervisorctl status
```

To restart a specific program:
```bash
docker exec <container-name> supervisorctl restart octane
docker exec <container-name> supervisorctl restart queue-worker:*
```

To view logs:
```bash
docker exec <container-name> tail -f /var/log/supervisor/octane.log
docker exec <container-name> tail -f /var/log/supervisor/queue-worker.log
```

