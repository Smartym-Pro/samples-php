<?php

namespace ServiceBundle\Model\Collections;

use Doctrine\Common\Collections\Criteria AS DoctrineCriteria;
use DomainBundle\Model\CriteriaInterface;

/**
 * Criteria for filtering Selectable collections.
 *
 * @author Benjamin Eberlei <kontakt@beberlei.de>
 * @since 2.3
 */
class Criteria extends DoctrineCriteria implements CriteriaInterface
{
}

