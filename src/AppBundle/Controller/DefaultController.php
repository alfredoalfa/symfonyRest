<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;


class DefaultController extends Controller
{
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
    }

    public function loginAction(Request $request)
    {        
         $helpers = $this->get(Helpers::class);
        // var_dump($helpers);
        
     //Recibir json POST
        $json = $request->get('json', null);
        
        //Array a devolver por defecto
        $data = array(
            'status' => 'error',
            'data' => 'Send json via post!'
        );
        // dump($json);
        if ($json != null) {
            // me haces login

            //convertir un json a un objeto dfe php
            $params = json_decode($json);

            $email = (isset($params->email)) ? $params->email : null;
            $password = (isset($params->password)) ? $params->password : null;
            $getHash = (isset($params->getHash)) ? $params->getHash : null;

            $emailConstraint = new Assert\Email([
                'message'=>'This is not the corect email format'
            ]);
            $validate_email= $this->get('validator')->validate($email, $emailConstraint);
// dump($getHash);
// die();
            if ($email != null && count($validate_email) == 0 && $password != null ){

                $jwt_auth = $this->get(JwtAuth::class);

                if ($getHash == null || $getHash == false) {
                    $pwd = hash('sha256', $password);
                    $signup = $jwt_auth->signup($email, $pwd);   

                } else {
                    $pwd = hash('sha256', $password);
                    $signup = $jwt_auth->signup($email, $pwd, True);

                }
        // var_dump($signup);
        // dump($signup);
       // die();
             return $this->json($signup);
            
            }else{
                $data = array(
                    'status' => 'error',
                    'data' => 'Email or password incorrect'
                );
            }
        }

        return $helpers->json($data);


    //     echo "login";
    //    die();
    //     return new Response(
    //         '<html><body>Hello</body></html>'
    //     );
    }

    public function pruebaAction(Request $request)
    {
        $helpers = $this->get(Helpers::class);
        $jwt_auth =$this->get(JwtAuth::class);
        $token = $request->get('authorization', null);
    
        dump($token);
        dump($jwt_auth->checkToken($token));

        if ($token && $jwt_auth->checkToken($token)) {
            $em = $this->getDoctrine()->getManager();
            $userRepo = $em->getRepository('BackendBundle:Users');
            $users = $userRepo->findAll();
        
            return $helpers->json(array(
                'status' => 'success',
                'users' => $users));

        } else {
            return $helpers->json(array(
                'status' => 'error',
                'code'   => 401,
                'data'   => 'Authorization not valid!!'
            )); 
        }

       

    //    var_dump($users[0]);
    //    die(); 
    //     die();
    //    return new JsonResponse(array(
    //         'status' => 'success',
    //         'users' => $users[0]->getName() 
    //         ));
     }
}
