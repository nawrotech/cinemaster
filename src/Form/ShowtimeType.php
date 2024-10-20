<?php

namespace App\Form;

use App\Entity\MovieFormat;
use App\Entity\MovieMovieType;
use App\Entity\Showtime;
use App\Repository\ShowtimeRepository;
use App\Validator\OverlappingShowtimeInSameScreeningRoom;
use App\Validator\SameMoviePlayingInTwoRoomsAtTheSameTime;
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
                'class' => MovieFormat::class,
                "label" => "Select Movie",
                "choice_label" => function (MovieFormat $movieFormat): string {
                    return "
                        Title: {$movieFormat->getMovie()->getTitle()} 
                        Duration: {$movieFormat->getMovie()->getDurationInMinutes()} minutes
                        Format: {$movieFormat->getFormat()->getAudioVersion()}, {$movieFormat->getFormat()->getVisualVersion()}        
                    ";
                },
            ])
            ->add('price', NumberType::class, [
                "constraints" => [
                    new NotBlank(),
                    new Positive()
                ]
            ])
            ->add("advertisementTimeInMinutes", NumberType::class, [
                "label" => "Ads block in minutes",
                "constraints" => [
                    new NotBlank(),
                    new Positive()
                ]
            ])
            ->add('startsAt', DateTimeType::class, [
                "label" => "Set date",
            ])
            ->add("submit", SubmitType::class)
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                function (PostSubmitEvent $event): void {
                    $form = $event->getForm();
                    $showtime = $event->getData();


                    if ($form->isValid()) {
                        $showtimeDuration = $showtime->getDuration();
                        $endsAt = $showtime->getStartsAt()->add(new DateInterval("PT{$showtimeDuration}M"));

                        $showtime->setEndsAt($endsAt);
                    };

                    if ($showtime->getEndsAt()) {
                        $overlappingViolations = $this->validator->validate($showtime, [
                                new SameMoviePlayingInTwoRoomsAtTheSameTime(),
                                new OverlappingShowtimeInSameScreeningRoom()
                            ]
                        );

                        if (!empty($overlappingViolations)) {
                            foreach($overlappingViolations as $violation) {
                                $form->addError(new FormError($violation->getMessage()));
                            }
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
