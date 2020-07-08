<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use App\Entity\Customer;
use App\Entity\Orders;
use App\Entity\Users;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\View;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;

class Newletters extends Controller {

    private $admin;

    public function __construct($routes)
    {
        parent::__construct($routes);

        $userId = Session::get('admin_id');
        $em = Entity::getEntityManager();
        if (is_null($admin = $em->find(Admin::class, $userId))) {
            return $this->redirectTo('/administration/login');
        }

        $this->admin = $admin;
    }

    public function indexAction() {
        CSRF::generate();

        if(Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            $usersFilter = (isset($params['send_all_users']));
            $customersFilter = (isset($params['send_all_customers']));

            $sendSubscribers = false;
            if(!empty($params['send_subscribers']) && $params['send_subscribers'] == 1) {
                $sendSubscribers = true;
            }

            if(!$sendSubscribers && ($usersFilter == false && $customersFilter == false)) {
                return View::render('Admin/newletters', ['admin' => $this->admin, 'page' => 'newletters', 'error' => true, 'params' => $params]);
            }

            $usersMinCommandFilter = (isset($params['users_min_commands'])) ? trim(intval($params['users_min_commands'])) : 0;
            $customersMinCommandFilter = (isset($params['customers_min_commands'])) ? trim(intval($params['customers_min_commands'])) : 0;

            $em = Entity::getEntityManager();
            $usersRepository = $em->getRepository(Users::class);
            $ordersRepository = $em->getRepository(Orders::class);
            $customersRepository = $em->getRepository(Customer::class);
            $newlettersRepository = $em->getRepository(\App\Entity\Newletters::class);

            $infos = [];

            if($usersFilter) {
                foreach ($usersRepository->findAll() as $user) {
                    $total = count($ordersRepository->findByUser($user));
                    if($total > $usersMinCommandFilter) {
                        $infos[] = ['email' => trim($user->getEmail()), 'lastname' => htmlspecialchars(trim($user->getLastname())), 'firstname' => htmlspecialchars(trim($user->getFirstname()))];
                    }
                }
            }

            if($customersFilter) {
                foreach ($customersRepository->findAll() as $customer) {
                    $total = count($ordersRepository->findByCustomer($customer));
                    if($total > $customersMinCommandFilter) {
                        $infos[] = ['email' => $customer->getEmail(), 'lastname' => $customer->getLastname(), 'firstname' => $customer->getFirstname()];
                    }
                }
            }

            if($sendSubscribers) {
                foreach ($newlettersRepository->findAll() as $item) {
                    $infos[] = ['email' => $item->getEmail(), 'lastname' => '', 'firstname' => ''];
                }
            }

            $subject = htmlspecialchars(trim($params['title']));

            ob_start();
            require(VIEWPATH . 'Admin/newletters/email.phtml');
            $message = ob_get_clean();

            foreach ($infos as $info) {
                $mail = new PHPMailer();
                $mail->isSMTP();
                $mail->CharSet = 'UTF-8';

                //Recipients
                $mail->setFrom('contact@drivncook.store', "Driv'N'Cook");
                $mail->addAddress($info['email'], $info['lastname'] . ' ' . $info['firstname']);

                // Content
                $mail->isHTML(true);
                $mail->Subject = $subject;
                $mail->Body    = $message;
                $mail->AltBody = htmlspecialchars(trim($params['text_plain']));

                $mail->send();
            }

            return View::render('Admin/newletters', ['admin' => $this->admin, 'page' => 'newletters', 'success' => true, 'params' => $params]);

        }

        return View::render('Admin/newletters', ['admin' => $this->admin, 'page' => 'newletters']);
    }
}