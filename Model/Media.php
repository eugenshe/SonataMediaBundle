<?php

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\MediaBundle\Model;

use Imagine\Image\Box;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;
use Symfony\Component\Validator\ExecutionContextInterface as LegacyExecutionContextInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;

/**
 * @method void setCategory($category)
 */
abstract class Media implements MediaInterface
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var bool
     */
    protected $enabled = false;

    /**
     * @var string
     */
    protected $providerName;

    /**
     * @var int
     */
    protected $providerStatus;

    /**
     * @var string
     */
    protected $providerReference;

    /**
     * @var array
     */
    protected $providerMetadata = [];

    /**
     * @var int
     */
    protected $width;

    /**
     * @var int
     */
    protected $height;

    /**
     * @var float
     */
    protected $length;

    /**
     * @var string
     */
    protected $copyright;

    /**
     * @var string
     */
    protected $authorName;

    /**
     * @var string
     */
    protected $context;

    /**
     * @var bool
     */
    protected $cdnIsFlushable;

    /**
     * @var string
     */
    protected $cdnFlushIdentifier;

    /**
     * @var \DateTime
     */
    protected $cdnFlushAt;

    /**
     * @var int
     */
    protected $cdnStatus;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var mixed
     */
    protected $binaryContent;

    /**
     * @var string
     */
    protected $previousProviderReference;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @var int
     */
    protected $size;

    /**
     * @var GalleryHasMediaInterface[]
     */
    protected $galleryHasMedias;

    /**
     * @var CategoryInterface
     */
    protected $category;

    /**
     * {@inheritdoc}
     */
    public function __toString()
    {
        return $this->getName() ?: 'n/a';
    }

    // NEXT_MAJOR: Remove this method
    public function __set($property, $value)
    {
        if ($property == 'category') {
            if (null !== $value && !is_a($value, 'Sonata\ClassificationBundle\Model\CategoryInterface')) {
                throw new \InvalidArgumentException(
                    '$category should be an instance of Sonata\ClassificationBundle\Model\CategoryInterface or null'
                );
            }

            $this->category = $value;
        }
    }

    // NEXT_MAJOR: Remove this method
    public function __call($method, $arguments)
    {
        if ($method == 'setCategory') {
            $this->__set('category', current($arguments));
        }
    }

    public function prePersist()
    {
        $this->setCreatedAt(new \DateTime());
        $this->setUpdatedAt(new \DateTime());
    }

    public function preUpdate()
    {
        $this->setUpdatedAt(new \DateTime());
    }

    /**
     * @static
     *
     * @return string[]
     */
    public static function getStatusList()
    {
        return [
            self::STATUS_OK => 'ok',
            self::STATUS_SENDING => 'sending',
            self::STATUS_PENDING => 'pending',
            self::STATUS_ERROR => 'error',
            self::STATUS_ENCODING => 'encoding',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function setBinaryContent($binaryContent)
    {
        $this->previousProviderReference = $this->providerReference;
        $this->providerReference = null;
        $this->binaryContent = $binaryContent;
    }

    /**
     * {@inheritdoc}
     */
    public function resetBinaryContent()
    {
        $this->binaryContent = null;
    }

    /**
     * {@inheritdoc}
     */
    public function getBinaryContent()
    {
        return $this->binaryContent;
    }

    /**
     * {@inheritdoc}
     */
    public function getMetadataValue($name, $default = null)
    {
        $metadata = $this->getProviderMetadata();

        return isset($metadata[$name]) ? $metadata[$name] : $default;
    }

    /**
     * {@inheritdoc}
     */
    public function setMetadataValue($name, $value)
    {
        $metadata = $this->getProviderMetadata();
        $metadata[$name] = $value;
        $this->setProviderMetadata($metadata);
    }

    /**
     * {@inheritdoc}
     */
    public function unsetMetadataValue($name)
    {
        $metadata = $this->getProviderMetadata();
        unset($metadata[$name]);
        $this->setProviderMetadata($metadata);
    }

    /**
     * {@inheritdoc}
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * {@inheritdoc}
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * {@inheritdoc}
     */
    public function setProviderName($providerName)
    {
        $this->providerName = $providerName;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderName()
    {
        return $this->providerName;
    }

    /**
     * {@inheritdoc}
     */
    public function setProviderStatus($providerStatus)
    {
        $this->providerStatus = $providerStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderStatus()
    {
        return $this->providerStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function setProviderReference($providerReference)
    {
        $this->providerReference = $providerReference;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderReference()
    {
        return $this->providerReference;
    }

    /**
     * {@inheritdoc}
     */
    public function setProviderMetadata(array $providerMetadata = [])
    {
        $this->providerMetadata = $providerMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function getProviderMetadata()
    {
        return $this->providerMetadata;
    }

    /**
     * {@inheritdoc}
     */
    public function setWidth($width)
    {
        $this->width = $width;
    }

    /**
     * {@inheritdoc}
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * {@inheritdoc}
     */
    public function setHeight($height)
    {
        $this->height = $height;
    }

    /**
     * {@inheritdoc}
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * {@inheritdoc}
     */
    public function setLength($length)
    {
        $this->length = $length;
    }

    /**
     * {@inheritdoc}
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * {@inheritdoc}
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
    }

    /**
     * {@inheritdoc}
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * {@inheritdoc}
     */
    public function setAuthorName($authorName)
    {
        $this->authorName = $authorName;
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthorName()
    {
        return $this->authorName;
    }

    /**
     * {@inheritdoc}
     */
    public function setContext($context)
    {
        $this->context = $context;
    }

    /**
     * {@inheritdoc}
     */
    public function getContext()
    {
        return $this->context;
    }

    /**
     * {@inheritdoc}
     */
    public function setCdnIsFlushable($cdnIsFlushable)
    {
        $this->cdnIsFlushable = $cdnIsFlushable;
    }

    /**
     * {@inheritdoc}
     */
    public function getCdnIsFlushable()
    {
        return $this->cdnIsFlushable;
    }

    /**
     * {@inheritdoc}
     */
    public function setCdnFlushIdentifier($cdnFlushIdentifier)
    {
        $this->cdnFlushIdentifier = $cdnFlushIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function getCdnFlushIdentifier()
    {
        return $this->cdnFlushIdentifier;
    }

    /**
     * {@inheritdoc}
     */
    public function setCdnFlushAt(\DateTime $cdnFlushAt = null)
    {
        $this->cdnFlushAt = $cdnFlushAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getCdnFlushAt()
    {
        return $this->cdnFlushAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setUpdatedAt(\DateTime $updatedAt = null)
    {
        $this->updatedAt = $updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setCreatedAt(\DateTime $createdAt = null)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * {@inheritdoc}
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getContentType()
    {
        return $this->contentType;
    }

    /**
     * {@inheritdoc}
     */
    public function getExtension()
    {
        // strips off query strings or hashes, which are common in URIs remote references
        return preg_replace('{(\?|#).*}', '', pathinfo($this->getProviderReference(), PATHINFO_EXTENSION));
    }

    /**
     * {@inheritdoc}
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * {@inheritdoc}
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * {@inheritdoc}
     */
    public function setCdnStatus($cdnStatus)
    {
        $this->cdnStatus = $cdnStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function getCdnStatus()
    {
        return $this->cdnStatus;
    }

    /**
     * {@inheritdoc}
     */
    public function getBox()
    {
        return new Box($this->width, $this->height);
    }

    /**
     * {@inheritdoc}
     */
    public function setGalleryHasMedias($galleryHasMedias)
    {
        $this->galleryHasMedias = $galleryHasMedias;
    }

    /**
     * {@inheritdoc}
     */
    public function getGalleryHasMedias()
    {
        return $this->galleryHasMedias;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreviousProviderReference()
    {
        return $this->previousProviderReference;
    }

    /**
     * NEXT_MAJOR: Remove this method when bumping Symfony requirement to 2.8+.
     *
     * @param ClassMetadata $metadata
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        if (class_exists('Symfony\Component\Validator\Constraints\Expression')) {
            $method = 'isStatusErroneous';
        } else {
            $method = ['methods' => ['isStatusErroneous']];
        }
        $metadata->addConstraint(new Assert\Callback($method));
    }

    /**
     * @param ExecutionContextInterface|LegacyExecutionContextInterface $context
     */
    public function isStatusErroneous($context)
    {
        if ($this->getBinaryContent() && $this->getProviderStatus() == self::STATUS_ERROR) {
            // Interface compatibility, the new ExecutionContextInterface should be typehinted when support for Symfony <2.5 is dropped
            if (!$context instanceof ExecutionContextInterface && !$context instanceof LegacyExecutionContextInterface) {
                throw new \InvalidArgumentException('Argument 1 should be an instance of Symfony\Component\Validator\ExecutionContextInterface or Symfony\Component\Validator\Context\ExecutionContextInterface');
            }

            if ($context instanceof LegacyExecutionContextInterface) {
                $context->addViolationAt('binaryContent', 'invalid', [], null);
            } else {
                $context->buildViolation('invalid')
                   ->atPath('binaryContent')
                   ->addViolation();
            }
        }
    }

    /**
     * @return CategoryInterface
     */
    public function getCategory()
    {
        return $this->category;
    }

    // NEXT_MAJOR: Uncomment this method and remove __call and __set
    // /**
    //  * @param CategoryInterface|null $category
    //  */
    // public function setCategory($category = null)
    // {
    //     $this->category = $category;
    // }
}
