<?php

namespace App\Controller;

use App\Entity\Candidature;
use App\Entity\Image;
use App\Entity\Job;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class JobController extends AbstractController
{
    // creer un form pour ajouter une candidature
    #[Route('/job/create/condidature', name:"create_candidature")]
    public function createCandidature() :Response{
        $cand=new Candidature();
        $formBuilder=$this->createFormBuilder($cand);
        $formBuilder->add(
            "candidat",
            TextType::class,
            [ "label"=>"Nom Candidat",
              "attr"=>[
                "placeholder"=>"Nom candidat"
               ]
            ]
         );
        $formBuilder->add("contenu",TextType::class);
        $formBuilder->add('job', EntityType::class, [
            'class' => Job::class,
            'choice_label' => 'description',
         ])
        ->add("date",DateTimeType::class)
        ->add('valider',SubmitType::class);
        
        $form=$formBuilder->getForm();

        return $this->render("job/createcandidature.html.twig", [ "f"=> $form->createView() ]);

    }

    #[Route('/job/create-demo', name: 'app_job_demo_create')]
    public function demo(EntityManagerInterface $em): Response
    {
        //C+M(Entity)+View
        $job= new Job();
        $job->setType('CDI');
        $job->setCompany('GFI ');
        $job->setDescription('Ingénieur DEVops');
        $job->setEmail('contact@email.com');
        $job->setExpiresAt(new \DateTimeImmutable('+45 days'));

        $img= new Image();
        $img->setUrl("https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQ9uaAbs2x-jQKYwYZRa_WbiU_CZcXoFVSAdFUqmNQ3PQ&s=10");
        $img->setAlt("Job de reve !!");
        $job->setImage($img);

        // ajouter DEux candidatures
        $condidature= new Candidature();
        $condidature->setCandidat('Ali ben saleh')
              ->setContenu(" Ingenieeur mobile")
              ->setDate(new \DateTime)
              ->setJob($job);
         
        $condidature2= new Candidature();
        $condidature2->setCandidat('MAriem ben saleh')
              ->setContenu(" Ingenieeur mobile /web")
              ->setDate(new \DateTime)
              ->setJob($job);
        $em->persist($condidature);
        $em->persist($condidature2);

      
        $em->persist($job);// charger dans la RAM
        $em->flush();//la sauvgarde réelle des datas dans la Database
        return $this->render('job/create_demo.html.twig',[ "id"=> $job->getId() ]);
    }

    #[Route("/job/{id}", name:"job_info")] 
    public function show(EntityManagerInterface $em, int $id){
        $job=$em->getRepository(Job::class)->find($id);
        $listCand=$em->getRepository(Candidature::class)
        ->findBy(["job"=>$job]);//ou bien job=>$id
        return $this->render('job/show.html.twig',
        [ "job"=>$job, "listeC"=>$listCand ] );
    }

     #[Route("/job", name:"job_all")]
    public function index(EntityManagerInterface $em){
        $jobs=$em->getRepository(Job::class)->findAll();
        return $this->render('job/index.html.twig',["jobs"=>$jobs]);
    }

}
