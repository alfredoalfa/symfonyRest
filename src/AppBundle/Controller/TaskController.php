<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\Users;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class TaskController extends Controller
{

    public function newAction(Request $request)
    {
        echo "hola mundo";
        die();
        return $this->render('index.html.twig');
    }
    
}