<?php
namespace ZT\RssFeed\Model\Rss;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use ZT\Blog\Api\CategoryRepositoryInterface;
use ZT\Blog\Api\PostRepositoryInterface;

/**
 * Rss WarehouseProduct model.
 */
class BlogFeed
{
    const DEFAULT_SORT = 'publish_time';
    const DEFAULT_PAGE_SIZE = 6;

    /**
     * @var PostRepositoryInterface
     */
    private $postRepositoryInterface;

    /**
     * @var CategoryRepositoryInterface
     */
    private $categoryRepositoryInterface;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SortOrder
     */
    private $_sortOrder;

    /**
     * BlogPosts constructor.
     * @param PostRepositoryInterface $postRepositoryInterface
     * @param CategoryRepositoryInterface $categoryRepositoryInterface
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SortOrder $sortOrder
     */
    public function __construct(
        PostRepositoryInterface $postRepositoryInterface,
        CategoryRepositoryInterface $categoryRepositoryInterface,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SortOrder $sortOrder
    )
    {
        $this->postRepositoryInterface = $postRepositoryInterface;
        $this->categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_sortOrder = $sortOrder;
    }

    public function getCollection()
    {
        $blogPosts = [];
        try {
            $today = date("Y-m-d");
            /* filter for all the posts */
            $searchCriteriaBuilder = $this->searchCriteriaBuilder
                ->addFilter('publish_time', $today, 'lteq')
                ->addFilter('is_active', 1, 'eq');
            $sortOrder = $this->_sortOrder
                ->setField(self::DEFAULT_SORT)
                ->setDirection("DESC");
            $searchCriteriaBuilder
                ->setSortOrders([$sortOrder])
                ->setCurrentPage(1)
                ->setPageSize(self::DEFAULT_PAGE_SIZE);
            $searchCriteria = $searchCriteriaBuilder
                ->create();
            $list = $this->postRepositoryInterface
                ->getList($searchCriteria);
            $blogPosts = $list->getItems();
        } catch (Exception $e) {
            echo $e->getMessage();die();
        }
        return $blogPosts;
    }
}
