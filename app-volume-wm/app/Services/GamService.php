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

    /**
     * Get recent users of a Chromebook by serial number
     *
     * @param string $serialNumber The Chromebook serial number
     * @param int $limit Number of recent users to return (1-10)
     * @return array
     */
    public function getChromebookRecentUsers(string $serialNumber, int $limit = 1): array
    {
        $limit = max(1, min(10, $limit));
        return $this->execute([
            'info', 'cros', 'cros_sn', $serialNumber,
            'recentusers', 'listlimit', (string)$limit
        ]);
    }

    /**
     * Get Chromebooks recently used by a specific user email
     *
     * @param string $email The user's email address
     * @param int $limit Number of recent users per device to return (1-10)
     * @return array
     */
    public function getChromebooksByUser(string $email, int $limit = 1): array
    {
        $limit = max(1, min(10, $limit));
        return $this->execute([
            'config', 'csv_output_row_filter', "recentUsers.email:regex:{$email}",
            'print', 'cros', 'serialnumber', 'recentusers', 'listlimit', (string)$limit
        ]);
    }

    /**
     * Get all chromebooks with serial number and asset ID
     * Used for daily inventory sync
     *
     * @param int|null $timeout Extended timeout for large datasets (default 300s)
     * @return array
     */
    public function getAllChromebooks(?int $timeout = 300): array
    {
        return $this->execute([
            'config', 'csv_output_header_filter', 'serialNumber,annotatedAssetId',
            'print', 'cros', 'fields', 'serialnumber,annotatedAssetId'
        ], $timeout);
    }

    /**
     * Get the most recent user for a single chromebook by serial
     *
     * @param string $serialNumber The Chromebook serial number
     * @return array
     */
    public function getChromebookLastUser(string $serialNumber): array
    {
        return $this->execute([
            'info', 'cros', 'cros_sn', $serialNumber,
            'recentusers', 'listlimit', '1'
        ]);
    }

    /**
     * Get chromebooks from specific OUs with serial and asset ID
     * Used for OU-specific usage updates
     *
     * @param array $ous Array of OU paths (e.g., ['/Devices/ES', '/Students/ES'])
     * @param int|null $timeout Extended timeout for large datasets (default 300s)
     * @return array
     */
    public function getChromebooksByOUs(array $ous, ?int $timeout = 300): array
    {
        $args = [
            'config', 'csv_output_header_filter', 'serialNumber,annotatedAssetId',
            'print', 'cros',
            'cros_ous_and_children', implode(',', $ous),
            'fields', 'serialnumber,annotatedAssetId'
        ];

        return $this->execute($args, $timeout);
    }
}
