<?php

namespace App\Form;

use App\Entity\MovieMovieType;
use App\Entity\Showtime;
use App\Repository\ShowtimeRepository;
use DateInterval;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Event\PostSubmitEvent;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
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
            ->add("movieFormat", EntityType::class, [
                'class' => MovieMovieType::class,
                "label" => "Select Movie",
                'choice_label' => function (MovieMovieType $movieFormat): string {
                    return "
                        Title: {$movieFormat->getMovie()->getTitle()} 
                        Duration: {$movieFormat->getMovie()->getDurationInMinutes()} minutes
                        Format: {$movieFormat->getMovieType()->getAudioVersion()}, {$movieFormat->getMovieType()->getVisualVersion()}        
                    ";
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
            ->add('start_time', DateTimeType::class, [
                "label" => "Set date"
            ])
            ->add("create_showtime", SubmitType::class)
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                function (PostSubmitEvent $event): void {
                    $form = $event->getForm();
                    $showtime = $event->getData();

                    $movieFormat = $showtime->getMovieFormat();

                    if ($form->isValid()) {
                        $maintenanceTime = $showtime->getScreeningRoom()->getMaintenanceTimeInMinutes();
                        $breaktime = $showtime->getAdvertisementTimeInMinutes();
                        $movieDurationTime = $movieFormat->getMovie()->getDurationInMinutes();
                       
                        $additionalTime = $breaktime + $maintenanceTime + $movieDurationTime;

                        $startTime = clone $showtime->getStartTime();
                        $endTime = $startTime->add(new DateInterval("PT{$additionalTime}M"));

                        $showtime->setEndTime($endTime);
                    };

                    if ($showtime->getEndTime()) {
                        $overlappingShowtimes = $this->showtimeRepository->findOverlappingForRoom(
                            $showtime->getScreeningRoom(),
                            $showtime->getStartTime(),
                            $showtime->getEndTime(),
                            $showtime->getId()
                        );

                        if (!empty($overlappingShowtimes)) {
                            $form->addError(new FormError("Something else is currently playing in room {$showtime->getScreeningRoom()->getName()}"));
                        }

                        if (!empty($this->showtimeRepository->findOverlappingForMovie(
                            $movieFormat,
                            $showtime->getStartTime(),
                            $showtime->getEndTime(),
                            $showtime->getId()
                        ))) {
                            $form->addError(new FormError("Picked movie is already playing in "));
                        }
                    
                   

                    }

                }

            )
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Showtime::class,
        ]);
    }
}
