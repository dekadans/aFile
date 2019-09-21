<?php
/**
 * @var \Psr\Container\ContainerInterface $container
 * @var \lib\Repositories\TranslationRepository $translationRepository
 */

require_once '../../vendor/autoload.php';
$container = require '../../app/container.php';
$translationRepository = $container->get(\lib\Repositories\TranslationRepository::class);
header('Content-Type: text/javascript');
?>
const locale = '<?= $translationRepository->getLanguage() ?>';
const languageData = {
    <?= $translationRepository->getLanguage() ?> : <?= json_encode($translationRepository->getLanguageData()) ?>
};

let i18n = new VueI18n({
    locale : locale,
    messages : languageData
});
