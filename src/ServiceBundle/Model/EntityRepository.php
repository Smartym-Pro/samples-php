<?php

namespace ServiceBundle\Model;

use Doctrine\ORM\EntityRepository AS DoctrineEntityRepository;
use DomainBundle\Model\RepositoryInterface;

/**
 * An EntityRepository serves as a repository for entities with generic as well as
 * business specific methods for retrieving entities.
 *
 * This class is designed for inheritance and users can subclass this class to
 * write their own repositories with business-specific methods to locate entities.
 *
 * @since   2.0
 * @author  Benjamin Eberlei <kontakt@beberlei.de>
 * @author  Guilherme Blanco <guilhermeblanco@hotmail.com>
 * @author  Jonathan Wage <jonwage@gmail.com>
 * @author  Roman Borschel <roman@code-factory.org>
 */
class EntityRepository extends DoctrineEntityRepository  implements RepositoryInterface
{
    /**
     * Create new entity
     *
     * @return object
     */
    public function create()
    {
        $class = $this->getClassName();
        $user = new $class();

        return $user;
    }
}
