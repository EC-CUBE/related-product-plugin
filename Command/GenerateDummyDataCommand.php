<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RelatedProduct4\Command;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Eccube\Common\EccubeConfig;
use Eccube\Entity\Master\ProductStatus;
use Eccube\Entity\Product;
use Faker\Factory as Faker;
use Plugin\RelatedProduct4\Entity\RelatedProduct;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class GenerateDummyDataCommand extends Command
{
    /** @var string */
    protected static $defaultName = 'eccube:plugin:relatedproduct4:fixtures:generate';

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /** @var EccubeConfig */
    protected $eccubeConfig;

    public function __construct(
        EntityManagerInterface $entityManager,
        EccubeConfig $eccubeConfig
    ) {
        parent::__construct();
        $this->entityManager = $entityManager;
        $this->eccubeConfig = $eccubeConfig;
    }

    protected function configure()
    {
        $this
            ->setDescription('Dummy data generator')
            ->addOption('with-locale', null, InputOption::VALUE_REQUIRED, 'Set to the locale.', 'ja_JP')
            ->addOption('relatedproducts', null, InputOption::VALUE_REQUIRED, 'Number of Related Products.', 2)
            ->addOption('max-products', null, InputOption::VALUE_OPTIONAL, 'Maximum number of products for which related products can be registered.', 'all-products')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command generate of dummy data.

  <info>php %command.full_name%</info>

Generate of dummy data with images.

  <info>php %command.full_name% --without-image</info>

Generate of dummy data without images, use for options to faster.
;
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $locale = $input->getOption('with-locale');
        $numberOfProducts = $input->getOption('relatedproducts');
        $maxProducts = $input->getOption('max-products');
        if ($maxProducts === 'all-products') {
            $maxProducts = null;
        }

        $faker = Faker::create($locale);
        /** @var Product[] $Products */
        $Products = $this->createQueryBuilder($maxProducts)->getQuery()->getResult();

        foreach ($Products as $Product) {
            // @see https://github.com/fzaninotto/Faker/issues/1125#issuecomment-268676186
            gc_collect_cycles();
            switch ($output->getVerbosity()) {
                case OutputInterface::VERBOSITY_QUIET:
                    break;
                case OutputInterface::VERBOSITY_NORMAL:
                    $output->write('Product: id='.$Product->getId().' ');
                    break;
                case OutputInterface::VERBOSITY_VERBOSE:
                case OutputInterface::VERBOSITY_VERY_VERBOSE:
                case OutputInterface::VERBOSITY_DEBUG:
                    $output->writeln('Product: id='.$Product->getId().' '.$Product->getName().' ');
                    break;
            }
            $max = $this->eccubeConfig['related_product.max_item_count'];
            if ($max < $numberOfProducts) {
                $numberOfProducts = $max;
            }
            // 既存の関連商品を削除しておく
            $RelatedProducts = $Product->getRelatedProducts();
            foreach ($RelatedProducts as $RelatedProduct) {
                $Product->removeRelatedProduct($RelatedProduct);
                $this->entityManager->remove($RelatedProduct);
            }
            $this->entityManager->flush();

            $qb = $this->createQueryBuilder($numberOfProducts);
            $qb->andWhere($qb->expr()->neq('p.id', $Product->getId()));
            /** @var Product[] $ChildProducts */
            $ChildProducts = $qb->getQuery()->getResult();

            foreach ($ChildProducts as $ChildProduct) {
                $RelatedProduct = new RelatedProduct();
                $RelatedProduct
                    ->setProduct($Product)
                    ->setChildProduct($ChildProduct)
                    ->setContent($faker->paragraph());
                $Product->addRelatedProduct($RelatedProduct);
                $this->entityManager->persist($RelatedProduct);
                $this->entityManager->flush();

                switch ($output->getVerbosity()) {
                    case OutputInterface::VERBOSITY_QUIET:
                        break;
                    case OutputInterface::VERBOSITY_NORMAL:
                        $output->write('R');
                        break;
                    case OutputInterface::VERBOSITY_VERBOSE:
                    case OutputInterface::VERBOSITY_VERY_VERBOSE:
                    case OutputInterface::VERBOSITY_DEBUG:
                        $output->writeln(' Relate='.$ChildProduct->getId());
                        break;
                }
            }
            switch ($output->getVerbosity()) {
                case OutputInterface::VERBOSITY_QUIET:
                    break;
                default:
                    $output->writeln('');
            }
        }
    }

    /**
     * @param int|null $limit
     *
     * @return QueryBuilder
     */
    private function createQueryBuilder($limit = null)
    {
        /** @var QueryBuilder $qb */
        $qb = $this->entityManager->getRepository(Product::class)
            ->createQueryBuilder('p');

        $qb->where('p.Status in (:Status)')
            ->setParameter('Status', [ProductStatus::DISPLAY_SHOW, ProductStatus::DISPLAY_HIDE])
            ->setMaxResults($limit);

        return $qb;
    }
}
