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

/**
 * RelatedProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RelatedProductRepository extends EntityRepository
{
    /**
     * 子商品の削除
     * @param $Product
     */
    public function removeChildProduct($Product)
    {
        $em = $this->getEntityManager();
        $Children = $this->findBy(array('Product' => $Product));
        foreach ($Children as $Child) {
            $em->remove($Child);
        }
    }

    /**
     *show related product with status is display
     * @param $Product
     * @param $Disp
     * @return array
     */
    public function showRelatedProduct($Product, $Disp)
    {
        $query = $this->createQueryBuilder('rp')
            ->innerJoin('Eccube\Entity\Product', 'p', 'WITH', 'p.id = rp.ChildProduct')
            ->andWhere('rp.Product = :Product')
            ->andWhere('p.Status = :Disp')
            ->setParameter('Product', $Product)
            ->setParameter('Disp', $Disp)
            ->orderBy('rp.id')
            ->getQuery();

        return $query->getResult();
    }

}
