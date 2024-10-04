<?php

namespace App\Form;

use App\Entity\Movie;
use App\Entity\ScreeningRoom;
use App\Entity\Showtime;
use App\Repository\ShowtimeRepository;
use DateInterval;
use Faker\Core\Number;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Event\SubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ShowtimeType extends AbstractType
{

    public function __construct(
        private ValidatorInterface $validator,
        private ShowtimeRepository $showtimeRepository
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('movie', EntityType::class, [
                'class' => Movie::class,
                "label" => "Select Movie",
                'choice_label' => function (Movie $movie): string {
                    return "Title: {$movie->getTitle()} Duration: {$movie->getDurationInMinutes()} minutes";
                },
            ])
            ->add('price', NumberType::class, [
                "constraints" => [
                    new NotBlank(),
                    new Positive()
                ]
            ])
            ->add("advertisement_time_in_minutes", NumberType::class, [
                "label" => "Ads block in minutes",
                "constraints" => [
                    new NotBlank(),
                    new Positive()

                ]
            ])
            ->add('startTime', null, [
                'widget' => 'single_text',
            ])
            ->add("virtualScheduling", HiddenType::class, [
                "mapped" => false,
                "required" => false,
            ])
            ->add("create_showtime", SubmitType::class)
            // ->addEventListener(FormEvents::SUBMIT, function (SubmitEvent $event): void {
            //     $form = $event->getForm();
            //     $showtime = $event->getData();
            // })
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                function (PostSubmitEvent $event): void {
                    $form = $event->getForm();
                    $showtime = $event->getData();

                    if ($form->isValid()) {
                        $maintenanceTime = $showtime->getScreeningRoom()->getMaintenanceTimeInMinutes();
                        $movieDurationTime = $showtime->getMovie()->getDurationInMinutes();
                        $breaktime = $showtime->getAdvertisementTimeInMinutes();

                        $additionalTime = $breaktime + $maintenanceTime + $movieDurationTime;

                        $startTime = clone $showtime->getStartTime();
                        $endTime = $startTime->add(new DateInterval("PT{$additionalTime}M"));

                        $showtime->setEndTime($endTime);
                    };

                    if ($showtime->getEndTime()) {
                        // dd($this->showtimeRepository->findOverlapping(
                        //     $showtime->getScreeningRoom(),
                        //     $showtime->getStartTime(),
                        //     $showtime->getEndTime()
                        // ));

                        if (!empty($this->showtimeRepository->findOverlapping(
                            $showtime->getScreeningRoom(),
                            $showtime->getStartTime(),
                            $showtime->getEndTime()
                        ))) {
                            // $form["virtualScheduling"]->addError(new FormError("you suck"));
                            $form->addError(new FormError("you suck"));
                        }
                    }




                    // dd($event->getData());
                }

            )
            // ->add('endTime', null, [
            //     'widget' => 'single_text',
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Showtime::class,
        ]);
    }
}
