<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Form\Type;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

class MediaType extends AbstractType implements LoggerAwareInterface
{
    /**
     * @var Pool
     */
    protected $pool;

    /**
     * @var string
     */
    protected $class;

    /**
     * NEXT_MAJOR: When switching to PHP 5.4+, replace by LoggerAwareTrait.
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Pool   $pool
     * @param string $class
     */
    public function __construct(Pool $pool, $class)
    {
        $this->pool = $pool;
        $this->class = $class;
        $this->logger = new NullLogger();
    }

    /**
     * NEXT_MAJOR: When switching to PHP 5.4+, replace by LoggerAwareTrait.
     *
     * @param LoggerInterface $logger
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $dataTransformer = new ProviderDataTransformer($this->pool, $this->class, [
            'provider' => $options['provider'],
            'context' => $options['context'],
            'empty_on_new' => $options['empty_on_new'],
            'new_on_update' => $options['new_on_update'],
        ]);
        $dataTransformer->setLogger($this->logger);

        $builder->addModelTransformer($dataTransformer);

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            if ($event->getForm()->has('unlink') && $event->getForm()->get('unlink')->getData()) {
                $event->setData(null);
            }
        });

        $this->pool->getProvider($options['provider'])->buildMediaType($builder);

        // NEXT_MAJOR: Remove ternary and keep 'Symfony\Component\Form\Extension\Core\Type\CheckboxType' value.
        // (when requirement of Symfony is >= 2.8)
        $builder->add(
            'unlink',
            method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
                ? 'Symfony\Component\Form\Extension\Core\Type\CheckboxType'
                : 'checkbox',
            [
                'label' => 'widget_label_unlink',
                'mapped' => false,
                'data' => false,
                'required' => false,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['provider'] = $options['provider'];
        $view->vars['context'] = $options['context'];
    }

    /**
     * {@inheritdoc}
     *
     * NEXT_MAJOR: remove this method.
     *
     * @deprecated Remove it when bumping requirements to Symfony >=2.7
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $this->configureOptions($resolver);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => $this->class,
                'empty_on_new' => true,
                'new_on_update' => true,
                'translation_domain' => 'SonataMediaBundle',
            ])
            ->setRequired([
                'provider',
                'context',
            ]);

        // NEXT_MAJOR: Remove this hack when dropping support for symfony 2.3
        if (method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')) {
            $resolver
                ->setAllowedTypes('provider', 'string')
                ->setAllowedTypes('context', 'string')
                ->setAllowedValues('provider', $this->pool->getProviderList())
                ->setAllowedValues('context', array_keys($this->pool->getContexts()))
            ;
        } else {
            $resolver
                ->setAllowedTypes([
                    'provider' => 'string',
                    'context' => 'string',
                ])
                ->setAllowedValues([
                    'provider' => $this->pool->getProviderList(),
                    'context' => array_keys($this->pool->getContexts()),
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        // NEXT_MAJOR: Return 'Symfony\Component\Form\Extension\Core\Type\FormType'
        // (when requirement of Symfony is >= 2.8)
        return method_exists('Symfony\Component\Form\AbstractType', 'getBlockPrefix')
            ? 'Symfony\Component\Form\Extension\Core\Type\FormType'
            : 'form';
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'sonata_media_type';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->getBlockPrefix();
    }
}
