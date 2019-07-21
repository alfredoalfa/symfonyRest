<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\Users;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class UserController extends Controller
{
    public function newAction(Request $request)
    {
        $helpers = $this->get(Helpers::class); //llama al servicio.

        $json = $request->get("json", null);
        $params = json_decode($json);

        $data = array(
            'status' => 'error1',
            'code' => '400',
            'msg' => '1Error con el usuario'
        );
        //dump($params);
        //die();
        if ($json != null) {

            $createdAt = new \DateTime('now');
            $role = 'user';

            $email = (isset($params->email)) ? $params->email : null ;
            $name = (isset($params->name)) ? $params->name : null ;
            $surname = (isset($params->surname)) ? $params->surname : null ;
            $password = (isset($params->password)) ? $params->password : null ;
            
            $emailConstraint = new Assert\Email([
                'message'=>'This is not the corect email format'
            ]);
            $validate_email= $this->get('validator')->validate($email, $emailConstraint);

            if ($email != null && count($validate_email) == 0 && $password != null && $name != null){

                $user =  new Users;

                $user->setCreatedAt($createdAt);
                $user->setRol($role);
                $user->setEmail($email);
                $user->setName($name);
                $user->setSurname($surname);

                //Cifrar contraseña
                $pwd = hash('sha256', $password);
                $user->setPassword($pwd);

                $em = $this->getDoctrine()->getManager();
                $isset_user = $em->getRepository('BackendBundle:Users')->findBy(array(
                    'email' => $email 
                ));
                //dump($isset_user);
                //die();
                if (count($isset_user) == 0) {
                    $em->persist($user);
                    $em->flush();

                    $data = array(
                        'status' => 'success',
                        'code' => '200',
                        'msg' => 'success con el usuario',
                        'user' => $user
                    );

                } else {
                    $data = array(
                        'status' => '2error',
                        'code' => '400',
                        'msg' => '2Error con el usuario'
                    );
                }                
            }
        
        }
        
        return $helpers->json($data);
    }

    public function editAction(Request $request)
    {
        $helpers = $this->get(Helpers::class); //llama al servicio.
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('autorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        if ($authCheck) {
                //Entity manager
                $em = $this->getDoctrine()->getManager();

                //Conseguir los datos del usuario Identificado via token
                $identity = $jwt_auth->checkToken($token, true);

                //Conseguir objeto actualizar
                // $user = $em->getRepository('BackendBundle:Users')->findBy(array(
                //     'id' => $identity->id
                // ));
                $user = $em->getRepository('BackendBundle:Users')->find($identity->id);
                //var_dump($identity);
                //var_dump($user[0]->getId());
                //die();
                //Obtener datos del post
                $json = $request->get("json", null);
                $params = json_decode($json);
                
                //Array error por defecto
                $data = array(
                    'status' => 'error1',
                    'code' => '400',
                    'msg' => '1Error con el usuario'
                );

                if ($json != null) {
        
                    //$createdAt = new \DateTime('now');
                    $role = 'user';
        
                    $email = (isset($params->email)) ? $params->email : null ;
                    $name = (isset($params->name)) ? $params->name : null ;
                    $surname = (isset($params->surname)) ? $params->surname : null ;
                    $password = (isset($params->password)) ? $params->password : null ;
                    
                    $emailConstraint = new Assert\Email([
                        'message'=>'This is not the corect email format'
                    ]);
                    $validate_email= $this->get('validator')->validate($email, $emailConstraint);
        
                    if ($email != null && count($validate_email) == 0 && $name != null){
               
                        //$user->setCreatedAt($createdAt);
                        $user->setRol($role);
                        $user->setEmail($email);
                        $user->setName($name);
                        $user->setSurname($surname);
 
                        if ($password != null) {
                                 //Cifrar contraseña
                                $pwd = hash('sha256', $password);
                                $user->setPassword($pwd);
                        }
        
                        $isset_user = $em->getRepository('BackendBundle:Users')->findBy(array(
                            'email' => $email 
                        ));

                        if (count($isset_user) == 0 || $identity->email == $email ) {
                            $em->persist($user);
                            $em->flush();
        
                            $data = array(
                                'status' => 'success',
                                'code' => '200',
                                'msg' => 'Usuario Updated',
                                'user' => $user
                            );
        
                        } else {
                            $data = array(
                                'status' => '2error',
                                'code' => '400',
                                'msg' => '2Error usuario no Updated'
                            );
                        }                
                    }
                
                }
        } else {
            $data = array(
                'status' => '3error',
                'code' => '401',
                'msg' => '3Error de token'
            );
        }

        return $helpers->json($data);
    }
    
}