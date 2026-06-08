<?php

namespace Core\Form\Type\Example;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\DateTime;
use Core\Services\Api\Example\DTO\Request\MessagesRequestDTO;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ExampleMessagesType extends AbstractType
{
    public const MESSAGE_TYPE_DIRECTORIES = [
        MessagesRequestDTO::MESSAGE_TYPE_ALL => 'Все',
        MessagesRequestDTO::MESSAGE_TYPE_DELIVERED => 'Доставлено',
        MessagesRequestDTO::MESSAGE_TYPE_UNDELIVERED => 'Не доставлено',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('date_from', TextType::class, [
                'label' => 'Дата начала периода',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Введите дату начала периода.',
                    ]),
                    new DateTime([
                        'format' => \DateTimeInterface::ATOM,
                        'message' => 'Дата должна быть в формате ISO 8601 (например: 2025-08-27T09:00:00Z или 2025-08-27T09:00:00+03:00).',
                    ]),
                ],
                'attr' => [
                    'placeholder' => '2025-08-27T09:00:00Z',
                ],
            ])
            ->add('date_to', TextType::class, [
                'label' => 'Дата окончания периода',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Введите дату окончания периода.',
                    ]),
                    new DateTime([
                        'format' => \DateTimeInterface::ATOM,
                        'message' => 'Дата должна быть в формате ISO 8601 (например: 2025-12-29T23:59:59Z или 2025-12-29T23:59:59+03:00).',
                    ]),
                ],
                'attr' => [
                    'placeholder' => '2025-12-29T23:59:59Z',
                ],
            ])
            ->add('limit', IntegerType::class, [
                'label' => 'Лимит записей',
                'constraints' => [
                    new Range([
                        'min' => 1,
                        'max' => 100,
                        'minMessage' => 'Лимит должен быть не менее 1.',
                        'maxMessage' => 'Лимит не может превышать 100.',
                    ]),
                ],
            ])
            ->add('example_ids', CollectionType::class, [
                'label' => 'ID примеров',
                'entry_type' => IntegerType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Передайте IDs примеров.',
                    ]),
                    new Type([
                        'type' => 'array',
                        'message' => 'IDs примеров должны быть массивом цифр.',
                    ]),
                ],
            ])
            ->add('message_type', ChoiceType::class, [
                'label' => 'Тип сообщения',
                'choices' => array_keys(self::MESSAGE_TYPE_DIRECTORIES),
                'constraints' => [
                    new NotBlank([
                        'message' => 'Выберите тип сообщения',
                    ]),
                ],
            ])
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => MessagesRequestDTO::class,
        ]);
    }
}