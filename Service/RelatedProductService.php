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

namespace Plugin\RelatedProduct\Service;

use Eccube\Application;

class RelatedProductService
{
    private $app;

    private $reportType;

    private $termStart;

    private $termEnd;

    private $unit;

    public function __construct($app)
    {
        $this->app = $app;
    }

    public function setReportType($reportType)
    {
        $this->reportType = $reportType;

        return $this;
    }

    public function setTerm($termType, $request)
    {
        // termStart <= X < termEnd となるように整形する
        if ($termType === 'monthly') {
            $date = $request['monthly'];
            $start = $date->format("Y-m-01 00:00:00");
            $end = $date
                ->modify('+ 1 month')
                ->format("Y-m-01 00:00:00");

            $this
                ->setTermStart($start)
                ->setTermEnd($end);
        } else {
            $start = $request['term_start']
                ->format("Y-m-d 00:00:00");
            $end = $request['term_end']
                ->modify('+ 1 day')
                ->format("Y-m-d 00:00:00");

            $this
                ->setTermStart($start)
                ->setTermEnd($end);
        }

        // 集計単位をせってい
        if (isset($request['unit'])) {
            $this->unit = $request['unit'];
        }

        return $this;
    }

    private function setTermStart($term)
    {
        $this->termStart = $term;

        return $this;
    }

    private function setTermEnd($term)
    {
        $this->termEnd = $term;

        return $this;
    }

    public function getData()
    {
        $app = $this->app;

        $excludes = array(
            $app['config']['order_processing'],
            $app['config']['order_cancel'],
            $app['config']['order_pending'],
        );

        /* @var $qb \Doctrine\ORM\QueryBuilder */
        $qb = $app['orm.em']->createQueryBuilder();
        $qb
            ->select('o')
            ->from('Eccube\Entity\Order', 'o')
            ->andWhere('o.del_flg = 0')
            ->andWhere('o.order_date >= :start')
            ->andWhere('o.order_date <= :end')
            ->andWhere('o.OrderStatus NOT IN (:excludes)')
            ->setParameter(':excludes', $excludes)
            ->setParameter(':start', $this->termStart)
            ->setParameter(':end', $this->termEnd);

        $result = array();
        try {
            $result = $qb->getQuery()->getResult();
        } catch (NoResultException $e) {
        }

        return $this->convert($result);
    }

    private function convert($data)
    {
        $result = array();
        switch ($this->reportType) {
            case 'term':
                $result = $this->convertByTerm($data);
                break;
            case 'product':
                $result = $this->convertByProduct($data);
                break;
            case 'age':
                $result = $this->convertByAge($data);
                break;
        }

        return $result;
    }

    private function convertByTerm($data)
    {
        $start = new \DateTime($this->termStart);
        $end = new \DateTime($this->termEnd);

        $format = $this->formatUnit();

        $raw = array();
        $price = array();
        for ($start; $start < $end; $start = $start->modify('+ 1 Hour')) {
            $date = $start->format($format);
            $raw[$date] = array(
                'price' => 0,
                'time' => 0,
            );
            $price[$date] = 0;
        }

        foreach ($data as $Order) {
            /* @var $Order \Eccube\Entity\Order */
            $orderDate = $Order
                ->getOrderDate()
                ->format($format);
            $price[$orderDate] += $Order->getPaymentTotal();

            $raw[$orderDate]['price'] += $Order->getPaymentTotal();
            $raw[$orderDate]['time'] ++;
        }

        return array(
            'raw' => $raw,
            'graph' => array(
                'labels' => array_keys($price),
                'datasets' => array(
                    array(
                        'label' => "購入金額",
                        'fillColor' => 'rgba(255,255,255,0.0)',
                        'strokeColor' => 'rgba(151,187,205,1)',
                        'pointColor' => 'rgba(151,187,205,1)',
                        'pointStrokeColor' => '#fff',
                        'pointHighlightFill' => '#fff',
                        'pointHighlightStroke' => 'rgba(151,187,205,1)',
                        'data' => array_values($price),
                    ),
                ),
            )
        );
    }

    private function formatUnit()
    {
        $unit = array(
            'byDay' => 'm/d',
            'byMonth' => 'm',
            'byWeekDay' => 'D',
            'byHour' => 'H',
        );
        return $unit[$this->unit];
    }

    private function convertByProduct($data)
    {
        $products = array();
        foreach ($data as $Order) {
            /* @var $Order \Eccube\Entity\Order */
            $OrderDetails = $Order->getOrderDetails();
            foreach ($OrderDetails as $OrderDetail) {
                /* @var $OrderDetail \Eccube\Entity\OrderDetail */
                $ProductClass = $OrderDetail->getProductClass();
                $id = $ProductClass->getId();
                if (!array_key_exists($id, $products)) {
                    $products[$id] = array(
                        'ProductClass' => $ProductClass,
                        'total' => 0,
                        'quantity' => 0,
                        'price' => 0,
                        'time' => 0,
                    );
                }
                $products[$id]['total'] += $OrderDetail->getPriceIncTax();
                $products[$id]['quantity'] += $OrderDetail->getQuantity();
                $products[$id]['price'] = $OrderDetail->getPriceIncTax();
                $products[$id]['time'] ++;
            }
        }

        $result = array();
        $i = 0;
        foreach ($products as $product) {
            $result[] = array(
                'label' => $product['ProductClass']->getProduct()->getName(),
                'value' => $product['total'],
                'color' => $this->getColor($i),
                'highlight' => $this->getColor($i, 'highlight'),
            );
        }

        return array(
            'raw' => $products,
            'graph' => $result
        );
    }

    private function getColor($i, $type = 'color')
    {
        $map = array(
            array(
                'color' => '#F7464A',
                'highlight' => 'FF5A5E',
            ),
            array(
                'color' => '#46BFBD',
                'highlight' => '#5AD3D1',
            ),
            array(
                'color' => '#FDB45C',
                'highlight' => '#FFC870',
            ),
        );

        $no = $i % count($map);

        return $map[$no][$type];
    }

    private function convertByAge($data)
    {
        $raw = array();
        $result = array();
        $now = new \DateTime();
        foreach ($data as $Order) {
            /* @var $Order \Eccube\Entity\Order */
            $age = '未回答';

            $birth = $Order->getCustomer()->getBirth();
            if ($birth) {
                $age = floor($birth->diff($now)->format('Y')) . '代';
            }
            if (!array_key_exists($age, $result)) {
                $result[$age] = 0;
                $raw[$age] = array(
                    'total' => 0,
                    'time' => 0,
                );
            }
            $result[$age] += $Order->getPaymentTotal();
            $raw[$age]['total'] += $Order->getPaymentTotal();
            $raw[$age]['time'] ++;
        }

        return array(
            'raw' => $raw,
            'graph' => array(
                'labels' => array_keys($result),
                'datasets' => array(
                    array(
                        'label' => "My First dataset",
                        'fillColor' => "rgba(220,220,220,0.5)",
                        'strokeColor' => "rgba(220,220,220,0.8)",
                        'highlightFill' => "rgba(220,220,220,0.75)",
                        'highlightStroke' => "rgba(220,220,220,1)",
                        'data' => array_values($result),
                    ),
                ),
            )
        );
    }
}
