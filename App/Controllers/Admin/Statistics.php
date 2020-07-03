<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use Core\Controller;
use Core\Entity;
use Core\Session;

class Statistics extends Controller
{
    private $em;
    private $admin;
    public function __construct($routes)
    {
        parent::__construct($routes);
        $this->em = Entity::getEntityManager();

        $this->layout = 'Admin/assets/layout.phtml';
        $adminId = Session::get('admin_id');
        if (is_null($admin = $this->em->find(Admin::class, $adminId))) {
            return $this->redirectTo('/administration/login');
        }
        $this->admin = $admin;
    }
    public function indexAction()
    {
        $data['admin'] = $this->admin;
        $data['page'] = 'Statistique';
        $this->load_view('Admin/mainStat', $data);
    }
}