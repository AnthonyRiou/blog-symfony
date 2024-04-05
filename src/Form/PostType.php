<?php

namespace App\Form;

use App\Entity\Tags;
use App\Entity\Posts;
use App\Entity\Users;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\String\Slugger\AsciiSlugger;

class PostType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, ['required' => false])
            // ->add('slug', TextType::class, ['required' => false])
            ->add('summary', TextType::class, ['required' => false])
            ->add('content', TextareaType::class, ['required' => false]);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event)
        {
            /** @var Post $post */
            $post = $event->getData();
            if(null == $post->getSlug()&& null !== $post->getTitle()) 
            {
                $slugger = new AsciiSlugger();
                $post->setSlug($slugger->slug($post->getTitle())->lower());
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Posts::class,
        ]);
    }
}
