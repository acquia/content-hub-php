<?php

namespace Acquia\ContentHubClient;

/**
 * Defines events for the acquia_contenthub module.
 *
 * @see \Acquia\ContentHubClient\Event\GetCDFTypeEvent
 */
final class ContentHubLibraryEvents
{

    /**
     * The event fired to collect ContentHub CDF Types.
     *
     * Contenthub allows any different type of CDF. Fire off an event to return
     * the correct object depending on the type requested.
     *
     * @Event
     *
     * @see \Acquia\ContentHubClient\Event\GetCDFTypeEvent
     * @see \Acquia\ContentHubClient\EventSubscriber\ClientCDF::onGetCDFType()
     * @see \Acquia\ContentHubClient\EventSubscriber\DefaultCDF::onGetCDFType()
     *
     * @var string
     */
    const GET_CDF_CLASS = 'contenthub_library_get_cdf_class';
}
