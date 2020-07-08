<?php

namespace App\Controllers\Franchise;

use App\Entity\Users;
use Core\Controller;
use Core\Entity;
use Core\Session;
use App\Models\StatisticsModel;

class Statistics extends Controller
{
    private $em;
    private $user;
    private $statisticsModel;
    public function __construct($routes)
    {
        parent::__construct($routes);
        $this->em = Entity::getEntityManager();

        $this->statisticsModel = new StatisticsModel();
        $this->layout = 'Franchise/assets/layout.phtml';
        $userId = Session::get('user_id');
        if (is_null($user = $this->em->find(Users::class, $userId))) {
            return $this->redirectTo('/panel/login');
        }
        $this->user = $user;
    }
    public function indexAction()
    {
        $userId = $this->user->getId();
        $data['head'] = '<script src="https://cdn.jsdelivr.net/npm/chart.js@2.8.0"></script>';
        $data['user'] = $this->user;
        $data['countOrderForUser'] = $this->statisticsModel->getCountOrderForUserByMonth($userId);
        $data['incommingMoneyFromOrdersByMonthForUser'] = $this->statisticsModel->getIncommingMoneyFromOrdersByMonthForUser($userId);
        $data['breadcrumb'] = ['Statistics'];
        $data['page'] = 'Statistique';
        $this->load_view('Franchise/mainStat', $data);
    }
}