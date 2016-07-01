<?php
/*
* This file is part of EC-CUBE
*
* Copyright(c) 2000-2015 LOCKON CO.,LTD. All Rights Reserved.
* http://www.lockon.co.jp/
*
* For the full copyright and license information, please view the LICENSE
* file that was distributed with this source code.
*/

namespace Plugin\RelatedProduct\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Expr\Join;

/**
 * CategoryTotalCountRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RelatedProductRepository extends EntityRepository
{
    public function removeChildProduct($Product)
    {
        $em = $this->getEntityManager();
        $Children = $this->findBy(array('Product' => $Product));
        foreach ($Children as $Child) {
            $em->remove($Child);
        }
    }

    /**
     * 関連商品の配列を取得する (削除フラグを考慮)
     *
     * @param $Product
     * @return array
     */
    public function getChildProducts($Product)
    {
        $qb = $this->createQueryBuilder('rp');
        return $qb
            ->innerJoin('Eccube\Entity\Product', 'cp', Join::WITH, 'rp.ChildProduct = cp')
            ->andWhere('cp.del_flg = 0')
            ->andWhere('rp.Product = :Product')
            ->setParameter('Product', $Product)
            ->getQuery()
            ->getResult()
        ;
    }
}
