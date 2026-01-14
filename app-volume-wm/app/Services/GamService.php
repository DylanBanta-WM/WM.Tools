<?php

namespace App\Services;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Illuminate\Support\Facades\Log;

class GamService
{
    /**
     * The GAM container name
     */
    private string $containerName = 'app-docker-gam';

    /**
     * The GAM executable path in the container
     */
    private string $gamPath = '/home/gam/gam7/gam';

    /**
     * Default timeout for GAM commands (in seconds)
     */
    private int $timeout = 60;

    /**
     * Execute a GAM command
     *
     * @param array $arguments The GAM command arguments (e.g., ['info', 'user', 'email@domain.com'])
     * @param int|null $timeout Optional timeout override
     * @return array Returns ['success' => bool, 'output' => string, 'error' => string]
     */
    public function execute(array $arguments, ?int $timeout = null): array
    {
        $command = array_merge(
            ['docker', 'exec', $this->containerName, $this->gamPath],
            $arguments
        );

        $process = new Process($command);
        $process->setTimeout($timeout ?? $this->timeout);

        try {
            $process->run();

            $output = $process->getOutput();
            $error = $process->getErrorOutput();

            if ($process->isSuccessful()) {
                Log::info('GAM command executed successfully', [
                    'command' => implode(' ', $arguments),
                    'output_length' => strlen($output)
                ]);

                return [
                    'success' => true,
                    'output' => $output,
                    'error' => $error,
                    'exit_code' => $process->getExitCode()
                ];
            } else {
                Log::warning('GAM command failed', [
                    'command' => implode(' ', $arguments),
                    'exit_code' => $process->getExitCode(),
                    'error' => $error
                ]);

                return [
                    'success' => false,
                    'output' => $output,
                    'error' => $error,
                    'exit_code' => $process->getExitCode()
                ];
            }
        } catch (ProcessFailedException $e) {
            Log::error('GAM process failed', [
                'command' => implode(' ', $arguments),
                'exception' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'output' => '',
                'error' => $e->getMessage(),
                'exit_code' => $e->getProcess()->getExitCode()
            ];
        } catch (\Exception $e) {
            Log::error('GAM execution error', [
                'command' => implode(' ', $arguments),
                'exception' => $e->getMessage()
            ]);

            return [
                'success' => false,
                'output' => '',
                'error' => $e->getMessage(),
                'exit_code' => null
            ];
        }
    }

    /**
     * Get information about a user
     *
     * @param string $email The user's email address
     * @return array
     */
    public function getUserInfo(string $email): array
    {
        return $this->execute(['info', 'user', $email]);
    }
}
