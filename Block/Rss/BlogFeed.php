<?php
namespace ZT\RssFeed\Block\Rss;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use ZT\BlogTheme\Api\SettingRepositoryInterface;
use ZT\RssFeed\Model\Rss\BlogFeed as RssModel;
use Magento\Catalog\Helper\Image;
use Magento\Framework\View\Page\Title;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Rss\DataProviderInterface;
use Magento\Framework\App\Rss\UrlBuilderInterface;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\UrlInterface;

class BlogFeed extends AbstractBlock implements DataProviderInterface
{

    /**
     * @var Image
     */
    protected $imageHelper;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var BlogFeed
     */
    protected $rssModel;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlBuilderInterface
     */
    protected $rssUrlBuilder;

    /**
     * @var Title
     */
    protected $_pageTitle;

    /**
     * @var ScopeConfigInterface
     */
    protected $_storeConfig;

    /**
     * @var SettingRepositoryInterface
     */
    protected $_settingRepository;


    public function __construct(
        Context $context,
        RssModel $rssModel,
        UrlBuilderInterface $rssUrlBuilder,
        Image $imageHelper,
        Session $customerSession,
        Title $pageTitle,
        ScopeConfigInterface $storeConfig,
        SettingRepositoryInterface $settingRepository,
        array $data = []
    )
    {
        $this->imageHelper = $imageHelper;
        $this->customerSession = $customerSession;
        $this->rssModel = $rssModel;
        $this->rssUrlBuilder = $rssUrlBuilder;
        $this->storeManager = $context->getStoreManager();
        $this->_pageTitle = $pageTitle;
        $this->_storeConfig = $storeConfig;
        $this->_settingRepository = $settingRepository;
        parent::__construct($context, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->setCacheKey(
            'rss_blog_feed_'
            . $this->getRequest()->getParam('bid') . '_'
            . $this->getStoreId() . '_'
            . $this->customerSession->getId()
        );
        parent::_construct();
    }

    /**
     * {@inheritdoc}
     */
    public function getRssData()
    {
        $rssId = $this->getRequest()->getParam('bid');
        $siteTitle = __('Blog') .' '. $this->_pageTitle->getShort();
        $themeConfigData = $this->getThemeConfigData();
        $blogTitle = $themeConfigData['title'];
        $title = $blogTitle ? $blogTitle : $siteTitle;
        $blogUrl = $this->storeManager->getStore()->getBaseUrl()  . 'blog';
        $data = ['title' => $title, 'description' => $title, 'charset' => 'UTF-8', 'link' => $blogUrl];

        /** @var $product Product */
        foreach ($this->rssModel->getCollection() as $post) {
            if(!empty($post['featured_img'])) {
                $baseMediaUrl = $this->_urlBuilder->getBaseUrl(['_type' => UrlInterface::URL_TYPE_MEDIA]);
                $imageUrl = $baseMediaUrl . $post['featured_img'];
                $description = '
                    <table><tr>
                        <td><a href="%s"><img src="%s" border="0" align="left" height="75" width="75"></a></td>
                        <td  style="text-decoration:none;">%s</td>
                    </tr></table>
                ';

                $description = sprintf(
                    $description,
                    $blogUrl . $post['identifier'],
                    $imageUrl,
                    $post['short_content']
                );
            }else{
                $description = $post['short_content'];
            }

            $data['entries'][] = [
                'title' => $post['title'],
                'link' => $blogUrl . $post['identifier'],
                'description' => $description
            ];
        }
        return $data;
    }

    /**
     * @return int
     */
    protected function getStoreId()
    {
        $storeId = (int)$this->getRequest()->getParam('store_id');
        if ($storeId == null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        return $storeId;
    }

    /**
     * @return int
     */
    public function getCacheLifetime()
    {
        return 600;
    }

    /**
     * {@inheritdoc}
     */
    public function isAllowed()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getFeeds()
    {
        $result = [];
        if ($this->isAllowed()) {
            $feeds[] = [
                'label' => __('Blog Post'),
                'link' => $this->rssUrlBuilder->getUrl(['type' => 'blog_feed', 'bid' => 'post'])
            ];
            $result = ['group' => __('Blog'), 'feeds' => $feeds];
        }
        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function isAuthRequired()
    {
        return false;
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    private function getThemeConfigData(): array
    {
        $currentThemeId = $this->_storeConfig->getValue('ztblog/pwa_theme/selected_theme');
        $data = [];
        if (!$currentThemeId) return [];
        try {
            $setting = $this->_settingRepository->getById($currentThemeId);
            $data = $setting->getData();
        } catch (Exception $e) {

        }
        return $data;
    }
}
