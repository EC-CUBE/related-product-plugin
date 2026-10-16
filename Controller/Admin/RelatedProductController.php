<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * https://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Plugin\RelatedProduct44\Controller\Admin;

use Eccube\Controller\AbstractController;
use Eccube\Repository\CategoryRepository;
use Eccube\Repository\ProductRepository;
use Knp\Component\Pager\Pagination\SlidingPagination;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Class RelatedProductController.
 */
class RelatedProductController extends AbstractController
{
    /**
     * RelatedProductController constructor.
     *
     * @param CategoryRepository $categoryRepository
     * @param ProductRepository $productRepository
     * @param PaginatorInterface $paginator
     */
    public function __construct(
        protected CategoryRepository $categoryRepository,
        protected ProductRepository $productRepository,
        protected PaginatorInterface $paginator,
    ) {
    }

    /**
     * search product modal.
     *
     * @param Request $request
     * @param int|string|null $page_no ルートの requirements 経由では文字列, 第 1 ルート経由では null になる
     *
     * @return Response|array<string, mixed>|null
     */
    #[Route(path: '/%eccube_admin_route%/related_product/search/product', name: 'admin_related_product_search')]
    #[Route(path: '/%eccube_admin_route%/related_product/search/product/page/{page_no}', name: 'admin_related_product_search_product_page', requirements: ['page_no' => '\d+'])]
    #[Template('@RelatedProduct44/admin/modal_result.twig')]
    public function searchProduct(Request $request, $page_no = null)
    {
        if (!$request->isXmlHttpRequest()) {
            return null;
        }

        $pageCount = $this->eccubeConfig['eccube_default_page_count'];
        $session = $this->session;
        if ('POST' === $request->getMethod()) {
            log_info('get search data with parameters ', [
                'id' => $request->get('id'),
                'category_id' => $request->get('category_id'),
            ]);
            $page_no = 1;
            $searchData = ['id' => $request->get('id')];
            if ($categoryId = $request->get('category_id')) {
                if (!$this->isValidId($categoryId)) {
                    throw new BadRequestHttpException();
                }
                $searchData['category_id'] = $categoryId;
            }
            $session->set('eccube.plugin.related_product.product.search', $searchData);
            $session->set('eccube.plugin.related_product.product.search.page_no', $page_no);
        } else {
            $searchData = (array) $session->get('eccube.plugin.related_product.product.search');
            if (is_null($page_no)) {
                // セッションに残ったページ番号が無い・不正な場合は 1 ページ目にする
                $page_no = $session->get('eccube.plugin.related_product.product.search.page_no');
                $page_no = $this->isValidId($page_no) ? (int) $page_no : 1;
            } else {
                // ルートの requirements は桁数を制限しないため, 0 や int の範囲外はここで弾く
                if (!$this->isValidId($page_no)) {
                    throw new BadRequestHttpException();
                }
                $page_no = (int) $page_no;
                $session->set('eccube.plugin.related_product.product.search.page_no', $page_no);
            }
        }

        if (!empty($searchData['category_id'])) {
            // セッションに残った以前の検索条件も、不正な ID なら条件から外す
            $searchData['category_id'] = $this->isValidId($searchData['category_id'])
                ? $this->categoryRepository->find($searchData['category_id'])
                : null;
        }

        $qb = $this->productRepository->getQueryBuilderBySearchDataForAdmin($searchData);

        /** @var SlidingPagination<int, mixed> $pagination */
        $pagination = $this->paginator->paginate(
            $qb,
            $page_no,
            $pageCount,
            ['wrap-queries' => true]
        );

        return [
            'pagination' => $pagination,
        ];
    }

    /**
     * 整数の ID・ページ番号として扱える値か (DB の integer の範囲に収まる正の整数).
     */
    private function isValidId(mixed $value): bool
    {
        if (!is_int($value) && !is_string($value)) {
            return false;
        }

        return preg_match('/\A[1-9][0-9]{0,9}\z/', (string) $value) === 1 && (int) $value <= 2147483647;
    }
}
