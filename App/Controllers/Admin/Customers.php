<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use App\Entity\Customer;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\Validator;

/**
 * Class Admins, gère les actions sur les administrateurs
 * @package App\Controllers\Admin
 */
class Customers extends Controller
{

    private $admin;
    private $em;
    private $customerRepository;

    public function __construct($routes)
    {
        parent::__construct($routes);

        $userId = Session::get('admin_id');
        $this->em = Entity::getEntityManager();
        $this->layout = 'Admin/assets/layout.phtml';
        if (is_null($admin = $this->em->find(Admin::class, $userId))) {
            return $this->redirectTo('/administration/login');
        }
        $this->customerRepository = $this->em->getRepository(Customer::class);
        $this->admin = $admin;
    }

    public function indexAction()
    {
        CSRF::generate();

        $customers = $this->customerRepository->findAll();

        $data['admin'] = $this->admin;
        $data['customers'] = $customers;
        $data['admin'] = $this->admin;
        $data['page'] = 'customer';
        $data['breadcrumb'] = ['Customer', 'listing'];
        $this->load_view('Admin/customer/listing', $data);
    }

    public function addAction()
    {

        CSRF::generate();
        $data['admin'] = $this->admin;
        $data['page'] = 'add_customer';
        $data['breadcrumb'] = ['Customer', 'add'];

        if (Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            $fields = [];

            $customer = new Customer();

            $customer->setCreatedAt(new \DateTime());
            if (Validator::isValidEmail($params['email']) && !$this->customerRepository->findOneByEmail($params['email'])) {
                $customer->setEmail(trim($params['email']));
            } else {
                $fields['email'] = $params['email'];
            }

            if (Validator::isValidName($params['firstname'])) {
                $customer->setFirstName(trim($params['firstname']));
            } else {
                $fields['firstname'] = $params['firstname'];
            }

            if (Validator::isValidName($params['lastname'])) {
                $customer->setLastName(trim($params['lastname']));
            } else {
                $fields['lastname'] = $params['lastname'];
            }

            if ($params['password'] === $params['passwordConfirm'] && Validator::isGoodPassword($params['password'])) {
                $customer->setPassword(password_hash($params['password'], PASSWORD_DEFAULT));
            } else {
                $fields['password'] = true;
            }

            if (!empty($fields)) {
                $data['fields'] = $fields;
                $this->load_view('Admin/customer/form', $data);
                return;
            }

            $this->em->persist($customer);
            $this->em->flush();
            return $this->redirectTo('/administration/customer/edit/' . $customer->getId());
        }

        $this->load_view('Admin/customer/form', $data);
    }

    public function editAction()
    {
        if (is_null($customer = $this->customerRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/customer');
        }

        CSRF::generate();
        if (Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();
            $fields = [];

            if ($customer->getEmail() != $params['email']) {
                if (Validator::isValidEmail($params['email']) && !$this->customerRepository->findOneByEmail($params['email'])) {
                    $customer->setEmail(trim($params['email']));
                } else {
                    $fields['email'] = true;
                }
            }

            if ($customer->getFirstname() != $params['firstname']) {
                if (Validator::isValidName($params['firstname'])) {
                    $customer->setFirstname(trim($params['firstname']));
                } else {
                    $fields['firstname'] = true;
                }
            }

            if ($customer->getLastname() != $params['lastname']) {
                if (Validator::isValidName($params['lastname'])) {
                    $customer->setLastname(trim($params['lastname']));
                } else {
                    $fields['lastname'] = true;
                }
            }

            if (!empty($params['password'])) {
                if ($params['password'] === $params['passwordConfirm'] && Validator::isGoodPassword($params['password'])) {
                    $customer->setPassword(password_hash($params['password'], PASSWORD_DEFAULT));
                } else {
                    $fields['password'] = true;
                }
            }

            if (!empty($fields)) {
                $data = ['admin' => $this->admin, 'customer' => $customer, 'page' => 'administrators', 'fields' => $fields];
                $data['breadcrumbs'] = ['Customer', 'edit'];
                $this->load_view('Admin/customer/form', $data);
                return;
            }
            $this->em->flush();
        }

        $data['admin'] = $this->admin;
        $data['customer'] = $customer;
        $data['breadcrumbs'] = ['Customer', 'edit'];
        $data['page'] = 'edit_customer';
        $this->load_view('Admin/customer/form', $data);
    }

    public function deleteAction()
    {
        Request::assertPostOnly();

        if (is_null($customer = $this->customerRepository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo("/administration/customer");
        }

        CSRF::validate();

        $this->em->remove($customer);
        $this->em->flush();

        return $this->redirectTo("/administration/customer");
    }

    public function searchAction()
    {
        $params = Request::getAllParams();
        echo json_encode($this->customerRepository->search($params['q']));
    }
}
