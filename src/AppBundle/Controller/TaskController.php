<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints as Assert;
use BackendBundle\Entity\Tasks;
use AppBundle\Services\Helpers;
use AppBundle\Services\JwtAuth;

class TaskController extends Controller
{

    public function newAction(Request $request, $id = null)
    {
        $helpers = $this->get(Helpers::class); //llama al servicio.
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('autorization', null);
        $authCheck = $jwt_auth->checkToken($token);

        if ($authCheck) {
            $identity =  $jwt_auth->checkToken($token, true);
            $json = $request->get("json", null);

                if ($json != null) {
                    $params = json_decode($json);

                    $createAt = new \Datetime("now");
                    $updateAt = new \Datetime("now");

                    $user_id = ($identity->id != null) ? $identity->id : null;
                    $title = (isset($params->title)) ? $params->title : null; 
                    $description = (isset($params->description)) ? $params->description : null;
                    $status= (isset($params->status)) ? $params->status : null; 

                    if ($user_id != null && $title != null) {
                          //Crear tarea
                            $em = $this->getDoctrine()->getManager();
                            
                            $user = $em->getRepository('BackendBundle:Users')->find($user_id);

                            if ($id == null) {
                                # code...
                            
                                $task = new Tasks();
                                $task->setUser($user);
                                $task->setTitle($title);
                                $task->setDescription($description);
                                $task->setStatus($status);
                                $task->setCreatedAt($createAt);
                                $task->setUpdatedAt($updateAt);

                                $em->persist($task);
                                $em->flush();

                                $data = array(
                                    "status" => "Success NEW",
                                    "code" => 200,
                                    "data" => $task
                                );
                            }else{
				
                                $task = $em->getRepository('BackendBundle:Tasks')->find($id);
                                    
                                    if (isset($identity->id) && $identity->id == $task->getUser()->getId()) {
                                                                        
                                        $task->setTitle($title);
                                        $task->setDescription($description);
                                        $task->setStatus($status);
                                        $task->setUpdatedAt($updateAt);

                                        $em->persist($task);
                                        $em->flush();

                                        $data = array(
                                            "status" => "Success UPDATE",
                                            "code" => 200,
                                            "data" => $task
                                        );

                                    }else{
                                        $data = array(
                                            "status" => "Error",
                                            "code" => 400,
                                            "msg" => "Task update error, no eres dueño de la tarea"
                                        );
                                    }
                            }

                    }else{
                            $data = array(
                                "status" => "Error",
                                "code" => 400,
                                "msg" => "Task no creada paramers fail"
                            );
                    }

                    
                } else {
                    $data = array(
                        "status" => "Error",
                        "code" => 400,
                        "msg" => "Task no creada, Validation fail"
                    );
                }
                        
            $data = array(
                "status" => "Success",
                "code" => 200,
                "data" => $task
            );

        } else {
            $data = array(
                "status" => "Error",
                "code" => 400,
                "msg" => "Autorization no valid"
            );
        }
        
        return $helpers->json($data);
	}
	
	public function tasksAction(Request $request)
	{
		$helpers = $this->get(Helpers::class); //llama al servicio para transformar objetos json.
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('autorization', null);
		$authCheck = $jwt_auth->checkToken($token);
		// dump($token);
		// die();
		if ($authCheck) {
            $identity =  $jwt_auth->checkToken($token, true);

			$em = $this->getDoctrine()->getManager();
			
			$dql = "SELECT t FROM BackendBundle:Tasks t ORDER BY t.id DESC";

			$query = $em->CreateQuery($dql);
			

			$page = $request->query->getInt('page', 1);
			$paginator = $this->get('knp_paginator');
			$items_per_page = 10;

			$pagination = $paginator->paginate($query, $page, $items_per_page);
			$total_items_count = $pagination->getTotalItemCount();


			$data = array(
                "status" => "Success",
                "code" => 200,
                "total_items_count" => $total_items_count,
				"page_actual" => $page,
				"items_per_page" => $items_per_page,
				"total_pages" => ceil($total_items_count / $items_per_page),
				"date" => $pagination
			);

		}else{
			$data = array(
                "status" => "Error",
                "code" => 401,
                "msg" => "Autorization no valid"
            );
		}	

		return $helpers->json($data);
	}

	public function taskAction(Request $request, $id = null)
	{
		$helpers = $this->get(Helpers::class); //llama al servicio para transformar objetos json.
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('autorization', null);
		$authCheck = $jwt_auth->checkToken($token);

			if ($authCheck) {
				$identity =  $jwt_auth->checkToken($token, true);
				
				$em = $this->getDoctrine()->getManager();

				$task = $em->getRepository('BackendBundle:Tasks')->find($id);

					if ($task && is_object($task) && $identity->id == $task->getUser()->getId()) {
						
						$data = array(
							"status" => "Success",
							"code" => 200,
							"data" => $task
						);
					}else {
						$data = array(
							"status" => "Error",
							"code" => 404,
							"msg" => "Task not found"
						);
					}

			}else{
				$data = array(
					"status" => "Error",
					"code" => 401,
					"msg" => "Autorization no valid"
				);
			}
		return $helpers->json($data);

    }
    
    public function searchAction(Request $request, $search = null)
    {   
        $helpers = $this->get(Helpers::class); //llama al servicio para transformar objetos json.
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('autorization', null);
		$authCheck = $jwt_auth->checkToken($token);
		// dump($token);
		// die();
		if ($authCheck) {
            $identity =  $jwt_auth->checkToken($token, true);

            $em = $this->getDoctrine()->getManager();
            
            //Filtro
            $filter = $request->get('filter', null);    
            if (empty($filter)) {
                $filter = null;
            } elseif ($filter == 1) {
                    $filter = 'new';
            } elseif ($filter == 2) {
                $filter = 'todo';
            } else {
                $filter = 'finished';
            }

            //Order
            $order = $request->get('order', null);    
            if (empty($order) || $order == 2) {
                $order = 'DESC';
            } else {
                $order = 'ASC';
            }

            //Busqueda
            if ($search != null) {
                $dql = "SELECT t FROM BackendBundle:Tasks t "
                        ."WHERE t.user = $identity->id AND "
                        ."(t.title LIKE :search OR t.description LIKE :search) ";
            
            } else {
                $dql = "SELECT t FROM BackendBundle:Tasks t "
                        ."WHERE t.user = $identity->id";
            }
            //Set filter
            if ($filter != null) {
                $dql .= " AND t.status = :filter"; 
            }

            //Set order
            $dql .= " ORDER BY t.id $order";

            //Create Query
            $query = $em->createQuery($dql);

                    //Set parameter filter

                    if ($filter != null) {
                        $query->setParameter('filter',"$filter");
                    }
                    //Set parameter search

                    if (!empty($search)) {
                        $query->setParameter('search',"%$search%");
                    }
            
            //dump($query->getSQL());die;
            $task = $query->getResult();

            $data = array(
                "status" => "Success",
                "code" => 200,
                "data" => $task
            );
            
        // $query = $this->getEntityManager()->createQuery($dql);
        // var_dump($query->getSQL());die;
        }else{
            $data = array(
                "status" => "Error",
                "code" => 401,
                "msg" => "Autorization no valid"
            );
        }

        return $helpers->json($data);
    }
    
    public function removeAction(Request $request, $id = null)
    {
		$helpers = $this->get(Helpers::class); //llama al servicio para transformar objetos json.
        $jwt_auth = $this->get(JwtAuth::class);

        $token = $request->get('autorization', null);
		$authCheck = $jwt_auth->checkToken($token);

			if ($authCheck) {
				$identity =  $jwt_auth->checkToken($token, true);
				
				$em = $this->getDoctrine()->getManager();

				$task = $em->getRepository('BackendBundle:Tasks')->find($id);

					if ($task && is_object($task) && $identity->id == $task->getUser()->getId()) {
                        
                        $em->remove($task);
                        $em->flush();
                        
						$data = array(
							"status" => "Success",
							"code" => 200,
							"data" => $task
						);
					}else {
						$data = array(
							"status" => "Error",
							"code" => 404,
							"msg" => "Task not found"
						);
					}

			}else{
				$data = array(
					"status" => "Error",
					"code" => 401,
					"msg" => "Autorization no valid"
				);
			}
		return $helpers->json($data);
    }
}