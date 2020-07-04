<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use Core\Controller;
use Core\Entity;
use Core\Session;
use App\Models\StatisticsModel;

class Statistics extends Controller
{
    private $em;
    private $admin;
    private $statisticsModel;
    public function __construct($routes)
    {
        parent::__construct($routes);
        $this->em = Entity::getEntityManager();

        $this->statisticsModel = new StatisticsModel();
        $this->layout = 'Admin/assets/layout.phtml';
        $adminId = Session::get('admin_id');
        if (is_null($admin = $this->em->find(Admin::class, $adminId))) {
            return $this->redirectTo('/administration/login');
        }
        $this->admin = $admin;
    }
    public function indexAction()
    {
        $data['eventByNextMonth'] = $this->statisticsModel->getEventByNextMonth();
        $data['newCustomerByDay'] = $this->statisticsModel->getNewCustomerByDay();
        $data['incommingMoneyFromOrdersByDay'] = $this->statisticsModel->getIncommingMoneyFromOrdersByDay();
        $data['usageProducts'] = $this->statisticsModel->getUsageProducts();
        $data['admin'] = $this->admin;
        $data['page'] = 'Statistique';
        $this->load_view('Admin/mainStat', $data);
    }
}