<?php
declare(strict_types=1);

namespace App\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Console\ConsoleOptionParser;
use Cake\ORM\Locator\LocatorAwareTrait;

/**
 * Creates an admin_users record. This is the only way to provision an admin
 * account — there is intentionally no public registration form for /admin.
 */
class CreateAdminUserCommand extends Command
{
    use LocatorAwareTrait;

    protected function buildOptionParser(ConsoleOptionParser $parser): ConsoleOptionParser
    {
        $parser
            ->setDescription('Create an admin_users account for logging into /admin.')
            ->addOption('username', ['help' => 'Username for the new admin.'])
            ->addOption('password', ['help' => 'Password for the new admin (omit to be prompted).']);

        return $parser;
    }

    public function execute(Arguments $args, ConsoleIo $io): ?int
    {
        $username = $args->getOption('username') ?? $io->ask('Username:');
        $password = $args->getOption('password') ?? $io->ask('Password:');

        if (trim($username) === '' || trim($password) === '') {
            $io->error('Username and password cannot be empty.');

            return static::CODE_ERROR;
        }

        $AdminUsers = $this->getTableLocator()->get('AdminUsers');

        $existing = $AdminUsers->find()->where(['username' => $username])->first();
        if ($existing !== null) {
            $io->error(sprintf('A user named "%s" already exists.', $username));

            return static::CODE_ERROR;
        }

        $user = $AdminUsers->newEntity([
            'username' => $username,
            'password' => $password,
        ]);

        if (!$AdminUsers->save($user)) {
            $io->error('Could not create the user:');
            foreach ($user->getErrors() as $field => $errors) {
                $io->err(sprintf('- %s: %s', $field, implode(', ', $errors)));
            }

            return static::CODE_ERROR;
        }

        $io->success(sprintf('Admin user "%s" created.', $username));

        return static::CODE_SUCCESS;
    }
}