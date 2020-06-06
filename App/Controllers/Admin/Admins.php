<?php

namespace App\Controllers\Admin;

use App\Entity\Admin;
use Core\Controller;
use Core\CSRF;
use Core\Entity;
use Core\Request;
use Core\Session;
use Core\Validator;
use Core\View;

/**
 * Class Admins, gère les actions sur les administrateurs
 * @package App\Controllers\Admin
 */
class Admins extends Controller
{

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

    public function administratorsAction()
    {
        $em = Entity::getEntityManager();
        $repository = $em->getRepository(Admin::class);
        $administrators = $repository->findAll();

        return View::render('Admin/administrators', ['admin' => $this->admin, 'administrators' => $administrators, 'page' => 'administrators']);
    }

    public function addAdministratorsAction()
    {
        $em = Entity::getEntityManager();
        $repository = $em->getRepository(Admin::class);

        CSRF::generate();

        if (Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();

            $fields = [];

            $admin = new Admin();

            if (Validator::isValidEmail($params['email']) && !$repository->findOneByEmail($params['email'])) {
                $admin->setEmail(trim($params['email']));
            } else {
                $fields['email'] = $params['email'];
            }

            if (Validator::isValidName($params['firstname'])) {
                $admin->setFirstName(trim($params['firstname']));
            } else {
                $fields['firstname'] = $params['firstname'];
            }

            if (Validator::isValidName($params['lastname'])) {
                $admin->setLastName(trim($params['lastname']));
            } else {
                $fields['lastname'] = $params['lastname'];
            }

            if ($params['password'] === $params['passwordConfirm'] && Validator::isGoodPassword($params['password'])) {
                $admin->setPassword(password_hash($params['password'], PASSWORD_DEFAULT));
            } else {
                $fields['password'] = true;
            }

            if (!empty($fields)) {
                return View::render('Admin/addAdministrators', ['admin' => $this->admin, 'page' => 'administrators_add', 'fields' => $fields]);
            }

            $em->persist($admin);
            $em->flush();
            return $this->redirectTo('/administration/administrators/' . $admin->getId() . '/edit');
        }

        return View::render('Admin/addAdministrators', ['admin' => $this->admin, 'page' => 'administrators_add']);
    }

    public function editAdministratorsAction()
    {
        $em = Entity::getEntityManager();
        $repository = $em->getRepository(Admin::class);


        if (is_null($admin = $repository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo('/administration/administrators');
        }

        CSRF::generate();
        if (Request::isPost()) {
            CSRF::validate();
            $params = Request::getAllParams();
            $fields = [];

            if ($admin->getEmail() != $params['email']) {
                if (Validator::isValidEmail($params['email']) && !$repository->findOneByEmail($params['email'])) {
                    $admin->setEmail(trim($params['email']));
                    $fields['email'] = true;
                } else {
                    $fields['email'] = false;
                }
            }

            if ($admin->getFirstname() != $params['firstname']) {
                if (Validator::isValidName($params['firstname'])) {
                    $admin->setFirstname(trim($params['firstname']));
                    $fields['firstname'] = true;
                } else {
                    $fields['firstname'] = false;
                }
            }

            if ($admin->getLastname() != $params['lastname']) {
                if (Validator::isValidName($params['lastname'])) {
                    $admin->setLastname(trim($params['lastname']));
                    $fields['lastname'] = true;
                } else {
                    $fields['lastname'] = false;
                }
            }

            if (!empty($params['password'])) {
                if ($params['password'] === $params['passwordConfirm'] && Validator::isGoodPassword($params['password'])) {
                    $admin->setPassword(password_hash($params['password'], PASSWORD_DEFAULT));
                    $fields['password'] = true;
                } else {
                    $fields['password'] = false;
                }
            }

            $em->flush();
            return View::render('Admin/editAdministrators', ['admin' => $this->admin, 'admin' => $admin, 'page' => 'administrators', 'fields' => $fields]);
        }


        return View::render('Admin/editAdministrators', ['admin' => $this->admin, 'admin' => $admin, 'page' => 'administrators']);
    }

    public function deleteAdministratorsAction()
    {
        Request::assertPostOnly();

        $em = Entity::getEntityManager();
        $repository = $em->getRepository(Admin::class);

        if (is_null($admin = $repository->find($this->getRouteParameter('id')))) {
            return $this->redirectTo("/administration/administrators");
        }

        CSRF::validate();

        $em->remove($admin);
        $em->flush();

        return $this->redirectTo("/administration/administrators");
    }
}
