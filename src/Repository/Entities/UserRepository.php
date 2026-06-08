<?php

namespace Core\Repository\Entities;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Core\Entity\Users;
use Core\Services\Cache\RedisCacheService;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    private RedisCacheService $sessionRedisCache;

    public function __construct(ManagerRegistry $registry, ContainerInterface $container)
    {
        $this->sessionRedisCache = $container->get('app.cache.session_redis');
        parent::__construct($registry, Users::class);
    }

    /**
     * Получение пользователя по сессии.
     *
     * @param string $sessionId
     * @return Users|null
     */
    public function getUserBySessionId(string $sessionId): ?Users
    {
        $userId = $this->getUserIdBySession($sessionId);
        if (is_null($userId)) {
            return null;
        }

        return $this->find($userId);
    }

    public function loadUserByIdentifier(int $identifier): ?Users
    {
        return $this->find($identifier);
    }

    /**
     * @deprecated
     *
     * @param string $username
     * @return null
     */
    public function loadUserByUsername(string $username)
    {
        return null;
    }

    private function getUserIdBySession(string $sessionId): ?int
    {
        $userId = (int)$this->sessionRedisCache->get($sessionId);
        if ($userId === 0) {
            return null;
        }

        return $userId;
    }
}