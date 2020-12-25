<?php
namespace ZT\RssFeed\Model\ResourceModel\Sitemap\Blog;

use Magento\Framework\App\ResourceConnection;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Zend_Db_Statement_Exception;
use Magento\Framework\EntityManager\EntityManager;
use Magento\Framework\EntityManager\MetadataPool;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Sitemap blog post collection model
 *
 * @api
 * @since 100.0.2
 */
class Post extends AbstractDb
{
    /**
     * @var MetadataPool
     * @since 100.1.0
     */
    protected $metadataPool;

    /**
     * @var EntityManager
     * @since 100.1.0
     */
    protected $entityManager;

    /**
     * @var ResourceConnection
     */
    protected $_resource;

    /**
     * Post constructor.
     * @param Context $context
     * @param ResourceConnection $resource
     * @param null $connectionName
     */
    public function __construct(
        Context $context,
        ResourceConnection $resource,
        $connectionName = null
    ) {
        $this->_resource = $resource;
        parent::__construct($context, $connectionName);
    }

    /**
     * Init resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('ztpwa_blog_post', 'post_id');
    }

    /**
     * @inheritDoc
     * @since 100.1.0
     */
    public function getConnection()
    {
        return $this->_resource->getConnection();
    }

    /**
     * @param $storeId
     * @return array
     * @throws LocalizedException
     * @throws Zend_Db_Statement_Exception
     */
    public function getCollection($storeId)
    {
        $today = date("Y-m-d");
        $select = $this->getConnection()->select()->from(
            ['main_table' => $this->getMainTable()],
            [$this->getIdFieldName(), 'url' => 'identifier', 'updated_at' => 'update_time', 'featured_img']
        )->where(
            'main_table.is_active = 1'
        )->where(
            'main_table.publish_time < "' . $today . '"'
        );

        $posts = [];
        $query = $this->getConnection()->query($select);
        while ($row = $query->fetch()) {
            $post = $this->_prepareObject($row);
            $posts[$post->getId()] = $post;
        }
        return $posts;
    }

    /**
     * Prepare page object
     *
     * @param array $data
     * @return DataObject
     */
    protected function _prepareObject(array $data)
    {
        $object = new DataObject();
        $object->setId($data[$this->getIdFieldName()]);
        $object->setUrl($data['url']);
        $object->setUpdatedAt($data['updated_at']);
        $object->setFeaturedImg($data['featured_img']);

        return $object;
    }
}
