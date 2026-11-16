//<?php
/**
 * seo_schema_type
 * 
 * Schema.org type — structured data markup for the page.
 * Determines which JSON-LD schema block is generated.
 * 
 * Types (40 total):
 *   WebPage, AboutPage, ContactPage, FAQPage, CollectionPage, ItemPage,
 *   SearchResultsPage, QAPage — for regular pages
 *   Article, NewsArticle, BlogPosting, ScholarlyArticle, TechArticle,
 *   Report — for articles and posts
 *   Recipe, Course, HowTo, JobPosting, Event — for special content
 *   Product, Offer, AggregateOffer — for shop
 *   Person, ProfilePage — for user pages
 *   Book, VideoObject, Movie, TVSeries, TVEpisode — for media
 *   MusicRecording, MusicAlbum, MusicPlaylist, MusicGroup — for music
 *   Service, Organization, LocalBusiness — for business
 *   SoftwareApplication, WebApplication, MobileApplication — for apps
 *   Dataset — for data
 * 
 * BreadcrumbList is added automatically for every page.
 * 
 * @category	tv
 * @version 	1.0.7
 * @license 	http://www.gnu.org/copyleft/gpl.html GNU Public License (GPL)
 * @internal	@caption Schema.org type
 * @internal	@input_type dropdown
 * @internal	@input_default WebPage
 * @internal	@input_options WebPage||AboutPage||ContactPage||FAQPage||CollectionPage||ItemPage||SearchResultsPage||QAPage||Article||NewsArticle||BlogPosting||ScholarlyArticle||TechArticle||Report||Recipe||Course||HowTo||JobPosting||Event||Product||Offer||AggregateOffer||Person||ProfilePage||Book||VideoObject||Movie||TVSeries||TVEpisode||MusicRecording||MusicAlbum||MusicPlaylist||MusicGroup||Service||Organization||LocalBusiness||SoftwareApplication||WebApplication||MobileApplication||Dataset
 * @internal	@output_widget
 * @internal	@output_widget_params
 * @internal	@template_assignments *
 * @internal	@modx_category SEO
 * @internal	@lock_tv 0
 * @internal    @installset base
 */