<?php

namespace App\Controllers\Api;

use App\Entity\Promotion;
use Core\Controller;
use Core\Entity;

class Promotions extends Controller {

    public function indexAction() {
        $em = Entity::getEntityManager();
        $promotionsRepository = $em->getRepository(Promotion::class);
        $allPromotions = $promotionsRepository->findAll();

        $promotions = [];

        foreach ($allPromotions as $promotion) {
            if(time() >= $promotion->getStartAt()->getTimestamp() && time() <= $promotion->getEndAt()->getTimestamp()) {
                $str = number_format($promotion->getReducPercentage(), 2) . "% de réduction chez ";
                $str .= htmlspecialchars(trim($promotion->getUser()->getSocietyName()));
                $str .= " pour une commande sur menu allant de ";
                $str .= number_format($promotion->getMinPrice(), 2) . "€ à ";
                $str .= number_format($promotion->getMaxPrice(), 2) . "€, offre valable jusqu'au ";
                $str .= $promotion->getEndAt()->format('d/m/Y H:i') . " !";

                $promotions[] = [
                    'text' => $str,
                    'id' => $promotion->getId()
                ];
            }
        }

        die(json_encode($promotions, JSON_UNESCAPED_UNICODE));
    }

}