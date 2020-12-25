<?php

namespace ZT\RssFeed\Model\ItemProvider\Blog;

use Magento\Sitemap\Model\ItemProvider\ItemProviderInterface;
use Magento\Sitemap\Model\ItemProvider\ConfigReaderInterface;
use ZT\RssFeed\Model\ResourceModel\Sitemap\Blog\PostFactory;
use Magento\Sitemap\Model\SitemapItemInterfaceFactory;

class Post implements ItemProviderInterface
{
    /**
     * Blog post factory
     *
     * @var PostFactory
     */
    private $postFactory;

    /**
     * Sitemap item factory
     *
     * @var SitemapItemInterfaceFactory
     */
    private $itemFactory;

    /**
     * Config reader
     *
     * @var ConfigReaderInterface
     */
    private $configReader;


    public function __construct(
        ConfigReaderInterface $configReader,
        PostFactory $postFactory,
        SitemapItemInterfaceFactory $itemFactory
    ) {
        $this->postFactory = $postFactory;
        $this->itemFactory = $itemFactory;
        $this->configReader = $configReader;
    }

    /**
     * {@inheritdoc}
     */
    public function getItems($storeId)
    {
        $collection = $this->postFactory->create()->getCollection($storeId);
        $items = array_map(function ($item) use ($storeId) {
            /** @var PostFactory $item */
            return $this->itemFactory->create([
                'url' => 'blog/' . $item->getUrl(),
                'updatedAt' => $item->getUpdateTime(),
                'priority' => $this->configReader->getPriority($storeId),
                'changeFrequency' => $this->configReader->getChangeFrequency($storeId),
            ]);
        }, $collection);

        return $items;
    }
}
