<?php
declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * Site logos. Each row is either a main logo (header and footer show only
 * the first one) or a partner logo (all of them on the homepage). The kind
 * lives in the options JSON, not in a separate categories table.
 */
class LogosTable extends Table
{
    public const TYPE_MAIN = 'main';
    public const TYPE_PARTNER = 'partner';

    public function initialize(array $config): void
    {
        parent::initialize($config);

        $this->setTable('logos');
        $this->setPrimaryKey('id');
        $this->addBehavior('Timestamp');
    }

    public function validationDefault(Validator $validator): Validator
    {
        $validator
            ->scalar('name')
            ->maxLength('name', 255)
            ->requirePresence('name', 'create')
            ->notEmptyString('name');

        return $validator;
    }

    /**
     * Logos of one kind that actually have an image, oldest first.
     *
     * @return array<\App\Model\Entity\Logo>
     */
    public function byType(string $type): array
    {
        $logos = [];
        foreach ($this->find()->orderBy(['id' => 'ASC'])->all() as $logo) {
            if (($logo->options['type'] ?? null) !== $type || $logo->path === '') {
                continue;
            }
            $logos[] = $logo;
        }

        return $logos;
    }

    /**
     * Header/footer logo. Several main logos may exist; only the first is used.
     */
    public function mainPath(): string
    {
        return $this->byType(self::TYPE_MAIN)[0]->path ?? '';
    }

    /**
     * Homepage partner strip. Every partner logo with an image.
     *
     * @return array<\App\Model\Entity\Logo>
     */
    public function partners(): array
    {
        return $this->byType(self::TYPE_PARTNER);
    }
}
