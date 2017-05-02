<?php
/**
 * DISCLAIMER
 * Do not edit or add to this file if you wish to upgrade this module to newer
 * versions in the future.
 *
 * @category  Smile
 * @package   Smile\ElasticsuiteCatalogOptimizer
 * @author    Fanny DECLERCK <fadec@smile.fr>
 * @copyright 2016 Smile
 * @license   Open Software License ("OSL") v. 3.0
 */
namespace Smile\ElasticsuiteCatalogOptimizer\Ui\Component\Optimizer\Form;

use Magento\Backend\Model\UrlInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Framework\Registry;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Smile\ElasticsuiteCatalogOptimizer\Api\OptimizerRepositoryInterface;
use Smile\ElasticsuiteCatalogOptimizer\Model\Optimizer;
use Smile\ElasticsuiteCatalogOptimizer\Model\ResourceModel\Optimizer\CollectionFactory as OptimizerCollectionFactory;

/**
 * Optimizer Data provider for adminhtml edit form
 *
 * @category Smile
 * @package  Smile\ElasticsuiteCatalogOptimizer
 * @author   Fanny DECLERCK <fadec@smile.fr>
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var OptimizerRepositoryInterface
     */
    private $optimizerRepository;

    /**
     * @var \Magento\Backend\Model\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    private $localeFormat;

    /**
     * DataProvider constructor
     *
     * @param string                       $name                       Component Name
     * @param string                       $primaryFieldName           Primary Field Name
     * @param string                       $requestFieldName           Request Field Name
     * @param OptimizerCollectionFactory   $optimizerCollectionFactory Optimizer Collection Factory
     * @param Registry                     $registry                   The Registry
     * @param RequestInterface             $request                    The Request
     * @param OptimizerRepositoryInterface $optimizerRepository        The Optimizer Repository
     * @param UrlInterface                 $urlBuilder                 URL Builder
     * @param FormatInterface              $localeFormat               Locale Format
     * @param array                        $meta                       Component Metadata
     * @param array                        $data                       Component Data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        OptimizerCollectionFactory $optimizerCollectionFactory,
        Registry $registry,
        RequestInterface $request,
        OptimizerRepositoryInterface $optimizerRepository,
        UrlInterface $urlBuilder,
        FormatInterface $localeFormat,
        array $meta = [],
        array $data = []
    ) {
        $this->collection          = $optimizerCollectionFactory->create();
        $this->registry            = $registry;
        $this->request             = $request;
        $this->optimizerRepository = $optimizerRepository;
        $this->urlBuilder          = $urlBuilder;
        $this->localeFormat        = $localeFormat;

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Get Component data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $optimizer = $this->getCurrentOptimizer();

        if ($optimizer) {
            $optimizerData = $optimizer->getData();
            if (!empty($optimizerData)) {
                $this->loadedData[$optimizer->getId()] = $optimizerData;
            }
            $this->loadedData[$optimizer->getId()]['preview_url']  = $this->getPreviewUrl($optimizer);
            $this->loadedData[$optimizer->getId()]['price_format'] = $this->localeFormat->getPriceFormat();
        }

        return $this->loadedData;
    }

    /**
     * Get current optimizer
     *
     * @return Optimizer
     * @throws NoSuchEntityException
     */
    private function getCurrentOptimizer()
    {
        $optimizer = $this->registry->registry('current_optimizer');

        if ($optimizer) {
            return $optimizer;
        }

        $requestId = $this->request->getParam($this->requestFieldName);
        if ($requestId) {
            $optimizer = $this->optimizerRepository->getById($requestId);
        }

        if (!$optimizer || !$optimizer->getId()) {
            $optimizer = $this->collection->getNewEmptyItem();
        }

        return $optimizer;
    }

    /**
     * Retrieve the optimizer Preview URL.
     *
     * @return string
     */
    private function getPreviewUrl()
    {
        $urlParams = ['ajax' => true];

        return $this->urlBuilder->getUrl('smile_elasticsuite_catalog_optimizer/optimizer/preview', $urlParams);
    }
}
