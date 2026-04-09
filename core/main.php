<?
$autoloadPath = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
}

class Render {
	//FRMWRK main logic script
	//v 1.0
	//MVC pattern
	public function Get() {
		$route = $this->Route();
	}

	private function Route() {
		include ('config.php');

		$routes = $this->GetRoutes();
		$type = (preg_match('/\b.php\b/',$routes['extension'])) ? 'file' : 'path';

		if ($routes['routes_count']==0) {
			if ($type=='path') {
				$this->DrawPage('main.php');
			}
		}
		else {
			if ($type=='file') {
				$this->DrawPage($routes['extension']);
			}
			else if ($type=='path') {
				$this->DrawPage($routes['routes'][1].'.php');
			}
		}
	}

	private function GetRoutes() {
		$result=array();

		$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
		$uriPath = parse_url($requestUri, PHP_URL_PATH);
		// Р вЂќР В»РЎРЏ РЎР‚Р С•РЎС“РЎвЂљР С‘Р Р…Р С–Р В° Р Р†РЎРѓР ВµР С–Р Т‘Р В° Р В±Р ВµРЎР‚Р ВµР С РЎвЂљР С•Р В»РЎРЉР С”Р С• PATH, РЎвЂЎРЎвЂљР С•Р В±РЎвЂ№ query-Р С—Р В°РЎР‚Р В°Р СР ВµРЎвЂљРЎР‚РЎвЂ№
		// (?logs_page=2, ?logs_per_page=100 Р С‘ РЎвЂљ.Р Т‘.) Р Р…Р Вµ Р В»Р С•Р СР В°Р В»Р С‘ Р С•Р С—РЎР‚Р ВµР Т‘Р ВµР В»Р ВµР Р…Р С‘Р Вµ РЎР‚Р С•РЎС“РЎвЂљР В°.
		$routesUri = (is_string($uriPath) && $uriPath !== '') ? $uriPath : '/';
		$routes = explode('/', $routesUri);
		$routes = array_filter($routes);
		$final = end($routes);
		$result['routes'] = array_diff($routes, array(''));
		$result['routes_count'] = count($routes);
		$result['extension'] = end($routes);
		$result['extension_type'] = substr($final, -4);
		
		$ref_check = (preg_match('/\bref=\b/',$result['extension'])) ? 'true' : 'false';
		$parameter_check = (preg_match('/\b=\b/',$result['extension'])) ? 'true' : 'false';
		$result['ref_check'] = $ref_check;
		$result['parameter_check'] = $parameter_check;
		
		if ($result['extension']!='' && $ref_check=='true' && $result['routes'][1]==$result['extension'] || $result['extension']!='' && $parameter_check=='true' && $result['routes'][1]==$result['extension']) {
			$result['routes'] = 0;
			$result['routes_count'] = 0;
			$result['extension_type'] = '';
			$result['extension'] = '';
		}
		$referal = (string)($_GET['referal'] ?? $_GET['referral'] ?? $_GET['ref'] ?? '');
		$result['referal'] = $referal;

		if ($referal !== '') {
			setcookie("referal", $referal, time() + 360000, '/');
		}

		$i = 0;
		$ii = 0;
		foreach ($routes as $route) {
			$i++;
			if ($i>2) {
				$ii++;
				$_GET[$ii]=$route;
			}
		}

		$routeFirst = (string)($result['routes'][1] ?? '');
			if ($routeFirst === 'member') {
				$routeSecond = (string)($result['routes'][2] ?? '');
				$result['routes'][1] = 'account';
				if ($routeSecond !== '') {
					$_GET['user'] = $routeSecond;
				}
				if ($result['routes_count'] === 1) {
					$result['extension'] = 'account';
				}
				$routeFirst = 'account';
			}
			if (in_array($routeFirst, ['cases', 'offers'], true)) {
				$result['routes'][1] = '404';
				$result['routes_count'] = 1;
				$result['extension'] = '404';
				$routeFirst = '404';
			}
			if ($routeFirst === 'use' && $this->IsApiGeoOnlineHost()) {
				$result['routes'][1] = 'tools';
				if ($result['routes_count'] === 1) {
					$result['extension'] = 'tools';
				}
			}
			if ($routeFirst === 'examples') {
				$routeSecond = (string)($result['routes'][2] ?? '');
				$routeThird = (string)($result['routes'][3] ?? '');
				if ($routeSecond !== '' && $routeSecond !== 'suggest' && $routeSecond !== 'article') {
					if ($routeThird !== '') {
						$_GET['cluster'] = $routeSecond;
						$_GET[1] = $routeThird;
						$result['routes'][2] = 'article';
						$result['extension'] = 'article';
					} else {
						$_GET['cluster'] = $routeSecond;
						$result['routes'][2] = 'main';
						$result['extension'] = 'main';
					}
				}
			}
			if ($this->IsApiGeoOnlineHost()) {
				if ($routeFirst === 'documentation') {
					$result['routes'][1] = 'docs';
					if ($result['routes_count'] === 1) {
						$result['extension'] = 'docs';
					}
				} elseif ($routeFirst === 'articles') {
					$result['routes'][1] = 'examples';
					if ($result['routes_count'] === 1) {
						$result['extension'] = 'examples';
					} else {
						$routeSecond = (string)($result['routes'][2] ?? '');
						$routeThird = (string)($result['routes'][3] ?? '');
						if ($routeSecond !== '' && $routeSecond !== 'suggest' && $routeSecond !== 'article') {
							if ($routeThird !== '') {
								$_GET['cluster'] = $routeSecond;
								$_GET[1] = $routeThird;
								$result['routes'][2] = 'article';
								$result['extension'] = 'article';
							} else {
								$_GET['cluster'] = $routeSecond;
								$result['routes'][2] = 'main';
								$result['extension'] = 'main';
							}
						}
					}
				}
			}
		
		
		return $result;
	}

	private function TemplatePath() {
		include ('config.php');

		$routes = $this->GetRoutes();
		$routeFirst = (string)($routes['routes'][1] ?? '');
		$templateSet = 'simple';

		if ($routeFirst !== '' && isset($TemplateRoutes[$routeFirst])) {
			$templateSet = (string)$TemplateRoutes[$routeFirst];
		} else {
			$domainShell = strtolower((string)($_SERVER['MIRROR_TEMPLATE_SHELL'] ?? 'simple'));
			if (in_array($domainShell, ['simple', 'dashboard', 'enterprise'], true)) {
				$templateSet = $domainShell;
			}
		}

		return 'views/'.$templateSet.'/';
	}

	private function DrawPage($FilePath) {
		$routes = $this->GetRoutes();
		include ('config.php');
		$perfEnabled = $this->PerfProfilerEnabled();
		$perfStartedAt = microtime(true);
		$perfMarks = [];
		$perfMark = function (string $label) use (&$perfMarks, $perfStartedAt, $perfEnabled): void {
			if (!$perfEnabled) {
				return;
			}
			$perfMarks[] = [
				'label' => $label,
				'time' => microtime(true) - $perfStartedAt,
			];
		};
		$perfMark('drawpage:start');

		$File = $FilePath;

		//include main framework lib
		include($LibsPath.'frmwrk/frmwrk.php');
		include($LibsPath.'geoip/geoip.php');
		$analyticsLib = $LibsPath.'analytics.php';
		$mirrorDomainsLib = $LibsPath.'mirror_domains.php';
		$mirrorRoutesLib = $LibsPath.'mirror_routes.php';
		$seoGeneratorSettingsLib = $LibsPath.'seo_generator_settings.php';
		$pageHtmlCacheLib = $LibsPath.'page_html_cache.php';
		$publicSiteUiLib = $LibsPath.'public_site_ui.php';
		$publicContactFormLib = $LibsPath.'public_contact_form.php';
		$publicPortalLib = $LibsPath.'public_portal.php';
		$publicServicesLib = $LibsPath.'public_services.php';
		$publicProjectsLib = $LibsPath.'public_projects.php';
		$publicCasesLib = $LibsPath.'public_cases.php';
		$examplesPopularityLib = $LibsPath.'examples_popularity.php';
		$footerSeoBlocksLib = $LibsPath.'footer_seo_blocks.php';
		if (file_exists($analyticsLib)) {
			include_once($analyticsLib);
		}
		if (file_exists($mirrorDomainsLib)) {
			include_once($mirrorDomainsLib);
		}
		if (file_exists($mirrorRoutesLib)) {
			include_once($mirrorRoutesLib);
		}
		if (file_exists($seoGeneratorSettingsLib)) {
			include_once($seoGeneratorSettingsLib);
		}
		if (file_exists($pageHtmlCacheLib)) {
			include_once($pageHtmlCacheLib);
		}
		if (file_exists($publicSiteUiLib)) {
			include_once($publicSiteUiLib);
		}
		if (file_exists($publicContactFormLib)) {
			include_once($publicContactFormLib);
		}
		if (file_exists($publicPortalLib)) {
			include_once($publicPortalLib);
		}
		if (file_exists($publicServicesLib)) {
			include_once($publicServicesLib);
		}
		if (file_exists($publicProjectsLib)) {
			include_once($publicProjectsLib);
		}
		if (file_exists($publicCasesLib)) {
			include_once($publicCasesLib);
		}
		if (file_exists($examplesPopularityLib)) {
			include_once($examplesPopularityLib);
		}
		if (file_exists($footerSeoBlocksLib)) {
			include_once($footerSeoBlocksLib);
		}
		$perfMark('libs:included');
		$FRMWRK = new FRMWRK();
		$perfMark('frmwrk:constructed');
		$GLOBAL['main_lib']=$LibName;
		$GLOBAL['lib_version']=$LibVersion;
		$_SERVER['MIRROR_TEMPLATE_KEY'] = 'simple';
		$_SERVER['MIRROR_TEMPLATE_SHELL'] = 'simple';
		$_SERVER['MIRROR_DOMAIN_HOST'] = (string)($_SERVER['HTTP_HOST'] ?? '');
		$_SERVER['MIRROR_TEMPLATE_MAIN_VIEW_FILE'] = 'main.php';
		$_SERVER['MIRROR_TEMPLATE_MODEL_FILE'] = 'main.php';
		$_SERVER['MIRROR_TEMPLATE_CONTROL_FILE'] = 'main.php';
		$_SERVER['MIRROR_GOOGLE_TAG_CODE'] = '';
		$_SERVER['MIRROR_YANDEX_COUNTER_CODE'] = '';

		$mirrorResolved = null;
		if (function_exists('mirror_domain_resolve')) {
			$mirrorResolved = mirror_domain_resolve($FRMWRK);
		}

		if (is_array($mirrorResolved)) {
			$_SERVER['MIRROR_TEMPLATE_KEY'] = (string)($mirrorResolved['template_view'] ?? 'simple');
			$_SERVER['MIRROR_DOMAIN_HOST'] = (string)($mirrorResolved['host'] ?? $_SERVER['MIRROR_DOMAIN_HOST']);
			$mirrorDomainRow = (array)($mirrorResolved['domain'] ?? []);
			$_SERVER['MIRROR_GOOGLE_TAG_CODE'] = (string)($mirrorDomainRow['google_tag_code'] ?? '');
			$_SERVER['MIRROR_YANDEX_COUNTER_CODE'] = (string)($mirrorDomainRow['yandex_counter_code'] ?? '');

			if (($mirrorResolved['allowed'] ?? false) !== true) {
				$fallbackHost = 'portcore.online';
				$requestUriRaw = (string)($_SERVER['REQUEST_URI'] ?? '/');
				if ($requestUriRaw === '') {
					$requestUriRaw = '/';
				}
				if ($requestUriRaw[0] !== '/') {
					$requestUriRaw = '/' . $requestUriRaw;
				}
				header('Location: https://' . $fallbackHost . $requestUriRaw, true, 302);
				exit;
			}
		}
		$perfMark('mirror:resolved');

		$previewTemplateKey = null;
		if (function_exists('mirror_template_preview_key')) {
			$previewTemplateKey = mirror_template_preview_key($FRMWRK);
		}
		if (is_string($previewTemplateKey) && $previewTemplateKey !== '') {
			$_SERVER['MIRROR_TEMPLATE_KEY'] = $previewTemplateKey;
		}

		$templateConfig = null;
		if (function_exists('mirror_template_resolve')) {
			$templateConfig = mirror_template_resolve($FRMWRK, (string)($_SERVER['MIRROR_TEMPLATE_KEY'] ?? 'simple'));
			$_SERVER['MIRROR_TEMPLATE_KEY'] = (string)($templateConfig['template_key'] ?? 'simple');
			$_SERVER['MIRROR_TEMPLATE_SHELL'] = (string)($templateConfig['shell_view'] ?? 'simple');
			$_SERVER['MIRROR_TEMPLATE_MAIN_VIEW_FILE'] = (string)($templateConfig['main_view_file'] ?? 'main.php');
			$_SERVER['MIRROR_TEMPLATE_MODEL_FILE'] = (string)($templateConfig['model_file'] ?? 'main.php');
			$_SERVER['MIRROR_TEMPLATE_CONTROL_FILE'] = (string)($templateConfig['control_file'] ?? 'main.php');
		}

		if (function_exists('public_contact_form_handle_request')) {
			public_contact_form_handle_request($FRMWRK);
		}
		if (function_exists('public_portal_handle_request')) {
			public_portal_handle_request($FRMWRK);
		}
		$perfMark('request_handlers:handled');

		$requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
		$requestPath = is_string($requestPath) && $requestPath !== '' ? $requestPath : '/';
		$pageHtmlCacheSettings = [];
		$pageHtmlCacheContext = [];
		$pageHtmlCacheEnabledForRequest = false;
		$pageHtmlCacheTtl = 0;
		if (preg_match('/^\/([A-Za-z0-9\-_]{8,128})\.txt$/', $requestPath, $keyMatch)) {
			$indexNowKey = trim((string)($GLOBALS['IndexNowKey'] ?? ''));
			if (function_exists('seo_gen_settings_get')) {
				$dbForSeoSettings = $FRMWRK->DB();
				if ($dbForSeoSettings instanceof mysqli) {
					$seoGenSettings = seo_gen_settings_get($dbForSeoSettings);
					if (is_array($seoGenSettings)) {
						$dbIndexNowKey = trim((string)($seoGenSettings['indexnow_key'] ?? ''));
						if ($dbIndexNowKey !== '') {
							$indexNowKey = $dbIndexNowKey;
						}
					}
				}
			}
			if (hash_equals($indexNowKey, (string)$keyMatch[1])) {
				while (ob_get_level() > 0) {
					ob_end_clean();
				}
				header('Content-Type: text/plain; charset=UTF-8');
				echo $indexNowKey;
				exit;
			}
		}
		if ($requestPath === '/sitemap.xml') {
			$this->RenderSitemapXml();
			exit;
		}
		if ($requestPath === '/sitemap-images.xml') {
			$this->RenderSitemapImagesXml();
			exit;
		}
		if ($requestPath === '/robots.txt') {
			$this->RenderRobotsTxt();
			exit;
		}
		if ($requestPath === '/blog' || $requestPath === '/blog/' || strpos($requestPath, '/blog/') === 0) {
			$tail = (string)substr($requestPath, strlen('/blog'));
			$target = '/journal' . $tail;
			$query = (string)($_SERVER['QUERY_STRING'] ?? '');
			if ($query !== '') {
				$target .= '?' . $query;
			}
			header('Location: ' . $target, true, 301);
			exit;
		}
		if (strpos($requestPath, '/examples/article/') === 0) {
			$tail = (string)substr($requestPath, strlen('/examples/article/'));
			$tail = ltrim($tail, '/');
			if ($tail !== '') {
				$target = '/journal/' . $tail;
				$query = (string)($_SERVER['QUERY_STRING'] ?? '');
				if ($query !== '') {
					$target .= '?' . $query;
				}
				header('Location: ' . $target, true, 301);
				exit;
			}
		}
		if ($this->IsApiGeoOnlineHost()) {
			if ($requestPath === '/tools' || strpos($requestPath, '/tools/') === 0) {
				$tail = (string)substr($requestPath, strlen('/tools'));
				$target = '/use' . $tail;
				$query = (string)($_SERVER['QUERY_STRING'] ?? '');
				if ($query !== '') {
					$target .= '?' . $query;
				}
				header('Location: ' . $target, true, 301);
				exit;
			}
			if ($requestPath === '/docs' || strpos($requestPath, '/docs/') === 0) {
				$tail = (string)substr($requestPath, strlen('/docs'));
				$target = '/documentation' . $tail;
				$query = (string)($_SERVER['QUERY_STRING'] ?? '');
				if ($query !== '') {
					$target .= '?' . $query;
				}
				header('Location: ' . $target, true, 301);
				exit;
			}
			if ($requestPath === '/examples' || strpos($requestPath, '/examples/') === 0) {
				$tail = (string)substr($requestPath, strlen('/examples'));
				if ($tail === '/article' || $tail === '/article/') {
					$tail = '/';
				} elseif (strpos($tail, '/article/') === 0) {
					$tail = (string)substr($tail, strlen('/article'));
				}
				$target = '/articles' . $tail;
				$query = (string)($_SERVER['QUERY_STRING'] ?? '');
				if ($query !== '') {
					$target .= '?' . $query;
				}
				header('Location: ' . $target, true, 301);
				exit;
			}
			if ($requestPath === '/articles/article' || $requestPath === '/articles/article/') {
				$target = '/articles/';
				$query = (string)($_SERVER['QUERY_STRING'] ?? '');
				if ($query !== '') {
					$target .= '?' . $query;
				}
				header('Location: ' . $target, true, 301);
				exit;
			}
			if (strpos($requestPath, '/articles/article/') === 0) {
				$tail = (string)substr($requestPath, strlen('/articles/article'));
				$target = '/articles' . $tail;
				$query = (string)($_SERVER['QUERY_STRING'] ?? '');
				if ($query !== '') {
					$target .= '?' . $query;
				}
				header('Location: ' . $target, true, 301);
				exit;
			}
			$isHomePath = ($requestPath === '/' || $requestPath === '/index.php');
			$isAssetPath = (bool)preg_match('/\.[a-z0-9]{1,8}$/i', (string)basename($requestPath));
			$isAllowedServicePath = (
				strpos($requestPath, '/dashboard') === 0
				|| $requestPath === '/use'
				|| strpos($requestPath, '/use/') === 0
				|| $requestPath === '/documentation'
				|| strpos($requestPath, '/documentation/') === 0
				|| $requestPath === '/articles'
				|| strpos($requestPath, '/articles/') === 0
				|| $requestPath === '/pricing'
				|| $requestPath === '/pricing/'
				|| $requestPath === '/terms'
				|| $requestPath === '/terms/'
				|| $requestPath === '/privacy'
				|| $requestPath === '/privacy/'
			);
			if (!$isHomePath && !$isAssetPath && !$isAllowedServicePath) {
				header('Location: /', true, 302);
				exit;
			}
		}

		$methodUpper = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
		if ($methodUpper === 'GET' && function_exists('footer_seo_blocks_handle_dynamic_request')) {
			$dbForFooterSeo = $FRMWRK->DB();
			if (footer_seo_blocks_handle_dynamic_request($dbForFooterSeo)) {
				exit;
			}
		}
		$isStaticAssetPath = (bool)preg_match('/\.[a-z0-9]{1,8}$/i', (string)basename($requestPath));
		$isFastSkipPath = (
			$requestPath === '/adminpanel' || strpos($requestPath, '/adminpanel/') === 0
			|| $requestPath === '/api' || strpos($requestPath, '/api/') === 0
			|| $requestPath === '/audit' || strpos($requestPath, '/audit/') === 0
			|| $requestPath === '/robots.txt'
			|| $requestPath === '/sitemap.xml'
			|| $requestPath === '/sitemap-images.xml'
		);
		if (!$isFastSkipPath && !$isStaticAssetPath && $methodUpper === 'GET' && function_exists('page_html_cache_get')) {
			$dbForPageCache = $FRMWRK->DB();
			if ($dbForPageCache instanceof mysqli) {
				$pageHtmlCacheSettings = page_html_cache_get($dbForPageCache);
				$pageHtmlCacheContext = page_html_cache_request_context($requestPath);
				if (page_html_cache_try_serve($pageHtmlCacheSettings, $pageHtmlCacheContext)) {
					exit;
				}
				$pageHtmlCacheEnabledForRequest = page_html_cache_is_cacheable($pageHtmlCacheSettings, $pageHtmlCacheContext);
				if ($pageHtmlCacheEnabledForRequest) {
					$pageHtmlCacheTtl = page_html_cache_ttl_for_path($pageHtmlCacheSettings, (string)($pageHtmlCacheContext['path'] ?? '/'));
				}
			}
		}
		$perfMark('page_cache:checked');

		$resolvedMirrorRoute = null;
		if (function_exists('mirror_routes_resolve')) {
			$resolvedMirrorRoute = mirror_routes_resolve($FRMWRK, $requestPath);
			if (is_array($resolvedMirrorRoute) && isset($resolvedMirrorRoute['model_page']) && is_array($resolvedMirrorRoute['model_page'])) {
				$_SERVER['MIRROR_ROUTE_MODEL_PAGE'] = $resolvedMirrorRoute['model_page'];
			}
		}

		// analytics: log each page visit (except internal technical routes)
		$routeFirst = (string)($routes['routes'][1] ?? '');
		$routeSecond = (string)($routes['routes'][2] ?? '');
		$skipAnalyticsRoutes = ['api', 'debug', 'pmyad', 'adminpanel'];
		if (function_exists('analytics_log_visit') && !in_array($routeFirst, $skipAnalyticsRoutes, true)) {
			$currentUser = $FRMWRK->GetCurrentUser();
			analytics_log_visit($FRMWRK, [
				'user_id' => $currentUser['id'] ?? null,
				'domain_host' => $_SERVER['MIRROR_DOMAIN_HOST'] ?? null
			]);

			// lead marker: user reached registration/auth screen
			if ($routeFirst === 'dashboard' && $routeSecond === 'auth' && ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'GET' && function_exists('analytics_log_lead_event')) {
				analytics_log_lead_event($FRMWRK, 'registration_page_view', [
					'user_id' => $currentUser['id'] ?? null,
					'email' => $currentUser['email'] ?? null,
					'meta' => ['path' => $_SERVER['REQUEST_URI'] ?? '/dashboard/auth/']
				]);
			}
		}
		$perfMark('analytics:logged');
		
		//include modules
		$GLOBAL['modules']=$FRMWRK->GetModules();
		foreach ($GLOBAL['modules'] as $module) {
			include ($LibsPath.'modules/'.$module.'/module.php');
			$$module = new Module();
		}
		$perfMark('modules:loaded');
		
		//drawing the page by routes rules
		if (is_array($resolvedMirrorRoute) && isset($resolvedMirrorRoute['paths']) && is_array($resolvedMirrorRoute['paths'])) {
			$resolvedPaths = $resolvedMirrorRoute['paths'];
			$modelDir = (string)($resolvedPaths['model_dir'] ?? '');
			$controlDir = (string)($resolvedPaths['control_dir'] ?? '');
			$viewDir = (string)($resolvedPaths['view_dir'] ?? '');
			$resolvedFile = (string)($resolvedPaths['file'] ?? '');

			if ($modelDir !== '') {
				$ModelsPath .= $modelDir;
			}
			if ($controlDir !== '') {
				$ControlsPath .= $controlDir;
			}
			if ($viewDir !== '') {
				$ViewsPath .= $viewDir;
			}
			if ($resolvedFile !== '') {
				$File = $resolvedFile;
			}
		} else {
			if ($routes['routes_count']>=2) {
				if ($routes['routes_count']!=0 && is_dir($ViewsPath.$routes['routes'][1])) {
					$ModelsPath = $ModelsPath.$routes['routes'][1].'/';
					$ControlsPath = $ControlsPath.$routes['routes'][1].'/';
					$ViewsPath = $ViewsPath.$routes['routes'][1].'/';
					$requestedFile = $routes['routes'][2].'.php';
					// Fallback for routes like /blog/{slug}/ where slug is handled inside main.php controller.
					if (file_exists($ViewsPath.$requestedFile)) {
						$File = $requestedFile;
					} else {
						$File = 'main.php';
					}
				}
			}
			else {
				if ($routes['routes_count']!=0 && is_dir($ViewsPath.$routes['routes'][1])) {
					$ModelsPath = $ModelsPath.$routes['routes'][1].'/';
					$ControlsPath = $ControlsPath.$routes['routes'][1].'/';
					$ViewsPath = $ViewsPath.$routes['routes'][1].'/';
					$File = 'main.php';
				}
			}
		}

		$modelFile = $File;
		$controlFile = $File;
		$isRootLanding = ($routes['routes_count'] == 0 || ($routes['routes_count'] == 1 && ($routes['routes'][1] ?? '') === ''));
		if ($File === 'main.php' && $isRootLanding) {
			$viewFileCandidate = (string)($_SERVER['MIRROR_TEMPLATE_MAIN_VIEW_FILE'] ?? 'main.php');
			$modelFileCandidate = (string)($_SERVER['MIRROR_TEMPLATE_MODEL_FILE'] ?? 'main.php');
			$controlFileCandidate = (string)($_SERVER['MIRROR_TEMPLATE_CONTROL_FILE'] ?? 'main.php');
			if (file_exists($ViewsPath.$viewFileCandidate)) {
				$File = $viewFileCandidate;
				$modelFile = $modelFileCandidate;
				$controlFile = $controlFileCandidate;
			}
		}
		
		if (file_exists($ViewsPath.$File)) {
			if (file_exists($ModelsPath.$modelFile)) {
				include($ModelsPath.$modelFile);
			}
		}
		else {
			$model404 = $ModelsPath.'404.php';
			$globalModel404 = DIR.'/core/models/404.php';
			if (file_exists($model404)) {
				include($model404);
			} elseif (file_exists($globalModel404)) {
				include($globalModel404);
			}
		}
		$perfMark('model:included');
		
		if (file_exists($ControlsPath.$controlFile)) {
			include($ControlsPath.$controlFile);
		}
		$perfMark('control:included');
		
		
		if (isset($routes['routes'][1]) && in_array($routes['routes'][1], $NoUImode, true)) {
			
			if (!file_exists($ViewsPath.$File)) {
				header("HTTP/1.0 404 Not Found");
			}

			if (file_exists($ViewsPath.$File)) {
				include($ViewsPath.$File);
			} else {
				include($FilesPath.'404.php');
			}

			if (!file_exists($ViewsPath.$File)) {
				die();
			}
		}
		else {
			if (!file_exists($ViewsPath.$File)) {
				header("HTTP/1.0 404 Not Found");
			}

			$templateViewPath = $this->TemplatePath();
			$templateHeaderFile = $TemplatePath.$templateViewPath.'header.php';
			$templateFooterFile = $TemplatePath.$templateViewPath.'footer.php';
			$mirrorDebugEnabled = (isset($_GET['mirror_debug']) && (string)$_GET['mirror_debug'] === '1');

			$renderBuffering = $pageHtmlCacheEnabledForRequest;
			if ($renderBuffering) {
				ob_start();
			}
			if ($pageHtmlCacheEnabledForRequest && !headers_sent()) {
				page_html_cache_send_headers($pageHtmlCacheTtl, 'MISS');
			}

			include($templateHeaderFile);
			$perfMark('template_header:included');

			if ($mirrorDebugEnabled) {
				$debugPayload = [
					'time' => date('c'),
					'request_uri' => (string)($_SERVER['REQUEST_URI'] ?? ''),
					'request_path' => (string)$requestPath,
					'http_host' => (string)($_SERVER['HTTP_HOST'] ?? ''),
					'mirror_domain_host' => (string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? ''),
					'mirror_template_key' => (string)($_SERVER['MIRROR_TEMPLATE_KEY'] ?? ''),
					'mirror_template_shell' => (string)($_SERVER['MIRROR_TEMPLATE_SHELL'] ?? ''),
					'template_view_path' => (string)$templateViewPath,
					'template_header_file' => (string)$templateHeaderFile,
					'template_header_exists' => is_file($templateHeaderFile),
					'template_footer_file' => (string)$templateFooterFile,
					'template_footer_exists' => is_file($templateFooterFile),
					'view_file' => (string)($ViewsPath.$File),
					'view_file_exists' => is_file($ViewsPath.$File),
					'model_file' => (string)($ModelsPath.$modelFile),
					'model_file_exists' => is_file($ModelsPath.$modelFile),
					'control_file' => (string)($ControlsPath.$controlFile),
					'control_file_exists' => is_file($ControlsPath.$controlFile),
					'mirror_domain_resolved' => is_array($mirrorResolved) ? $mirrorResolved : null,
					'mirror_template_resolved' => is_array($templateConfig) ? $templateConfig : null,
					'mirror_route_resolved' => is_array($resolvedMirrorRoute) ? $resolvedMirrorRoute : null,
				];

				echo '<div style="max-width:1240px;margin:12px auto 18px;padding:0 12px;">';
				echo '<div style="border:1px solid #6aa9ff;border-radius:10px;background:#0d1b2f;color:#d7e9ff;padding:12px 14px;">';
				echo '<div style="font:700 13px/1.2 Consolas,monospace;margin-bottom:8px;">Mirror Debug (?mirror_debug=1)</div>';
				echo '<pre style="margin:0;white-space:pre-wrap;word-break:break-word;font:12px/1.45 Consolas,monospace;">'
					. htmlspecialchars((string)json_encode($debugPayload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT), ENT_QUOTES, 'UTF-8')
					. '</pre>';
				echo '</div></div>';
			}

			if (file_exists($ViewsPath.$File)) {
				ob_start();
				include($ViewsPath.$File);
				$viewOutput = (string)ob_get_clean();
				if ($this->ViewStartsWithHtmlShell($viewOutput)) {
					header("HTTP/1.1 500 Internal Server Error");
					echo '<div style="max-width:840px;margin:36px auto;padding:0 16px;">';
					echo '<div style="border:1px solid #e06c75;border-radius:12px;padding:18px;background:#2a1115;color:#ffdfe3;">';
					echo '<h2 style="margin-top:0;">Layout Policy Violation</h2>';
					echo '<p>View files in UI mode must not contain full HTML shell tags. Use template header/footer from core renderer.</p>';
					echo '</div></div>';
				} else {
					echo $viewOutput;
				}
			}
			else {
				include($FilesPath.'404.php');
			}
			$perfMark('view:rendered');

			include($templateFooterFile);
			$perfMark('template_footer:included');

			if ($renderBuffering) {
				$fullPageOutput = (string)ob_get_clean();
				echo $fullPageOutput;
				page_html_cache_store($pageHtmlCacheSettings, $pageHtmlCacheContext, $fullPageOutput);
				$perfMark('page_cache:stored');
			}
			
			if (!file_exists($ViewsPath.$File)) {
				die();
			}
		}
		$perfMark('drawpage:end');
		$this->PerfProfilerFlush($perfEnabled, $perfStartedAt, $perfMarks, [
			'uri' => (string)($_SERVER['REQUEST_URI'] ?? '/'),
			'path' => (string)$requestPath,
			'host' => (string)($_SERVER['HTTP_HOST'] ?? ''),
			'template' => (string)($_SERVER['MIRROR_TEMPLATE_SHELL'] ?? ''),
			'file' => (string)$File,
			'route_first' => (string)($routes['routes'][1] ?? ''),
			'method' => (string)$methodUpper,
		]);
	}

	private function PerfProfilerEnabled(): bool
	{
		$method = strtoupper((string)($_SERVER['REQUEST_METHOD'] ?? 'GET'));
		if ($method !== 'GET') {
			return false;
		}
		$requestPath = parse_url((string)($_SERVER['REQUEST_URI'] ?? '/'), PHP_URL_PATH);
		$requestPath = is_string($requestPath) && $requestPath !== '' ? $requestPath : '/';
		if (
			$requestPath === '/robots.txt'
			|| $requestPath === '/sitemap.xml'
			|| $requestPath === '/sitemap-images.xml'
			|| strpos($requestPath, '/adminpanel') === 0
			|| strpos($requestPath, '/dashboard') === 0
			|| strpos($requestPath, '/api/') === 0
		) {
			return false;
		}
		return true;
	}

	private function PerfProfilerFlush(bool $enabled, float $startedAt, array $marks, array $context = []): void
	{
		if (!$enabled) {
			return;
		}

		$totalMs = (microtime(true) - $startedAt) * 1000;
		$force = isset($_GET['perf_debug']) && (string)$_GET['perf_debug'] === '1';
		if (!$force && $totalMs < 400) {
			return;
		}

		$parts = [];
		$prev = 0.0;
		foreach ($marks as $mark) {
			$current = (float)($mark['time'] ?? 0.0);
			$deltaMs = max(0.0, ($current - $prev) * 1000);
			$parts[] = (string)($mark['label'] ?? 'step') . '=' . number_format($deltaMs, 1, '.', '') . 'ms';
			$prev = $current;
		}

		$contextParts = [];
		foreach ($context as $key => $value) {
			$value = trim((string)$value);
			if ($value === '') {
				continue;
			}
			$contextParts[] = $key . '=' . $value;
		}

		error_log(
			'FRONT PERF total=' . number_format($totalMs, 1, '.', '') . 'ms'
			. (empty($contextParts) ? '' : ' ' . implode(' ', $contextParts))
			. ' :: ' . implode(' | ', $parts)
		);
	}

	private function ViewStartsWithHtmlShell(string $output): bool
	{
		$probe = ltrim(substr($output, 0, 480));
		if ($probe === '') {
			return false;
		}
		return (bool)preg_match('/^(?:<\?xml[^>]*>\s*)?(?:<!doctype\s+html\b|<html\b|<head\b|<body\b)/i', $probe);
	}

	private function CurrentBaseUrl(): string {
		$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
		$host = (string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '');
		$host = strtolower(trim($host));
		if (strpos($host, ':') !== false) {
			$host = explode(':', $host, 2)[0];
		}
		if ($host === '') {
			$host = 'localhost';
		}
		return $scheme.'://'.$host;
	}

	private function IsApiGeoOnlineHost(): bool
	{
		$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
		if (strpos($host, ':') !== false) {
			$host = explode(':', $host, 2)[0];
		}
		return in_array($host, ['apigeoip.online', 'www.apigeoip.online'], true);
	}

	private function RenderSitemapXml(): void {
		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		$base = $this->CurrentBaseUrl();
		$sitemapHost = strtolower((string)parse_url($base, PHP_URL_HOST));
		$isApiGeoOnlineSitemap = in_array($sitemapHost, ['apigeoip.online', 'www.apigeoip.online'], true) || $this->IsApiGeoOnlineHost();
		$urls = [];

		if ($isApiGeoOnlineSitemap) {
			$urls[] = ['loc' => $base.'/', 'changefreq' => 'daily', 'priority' => '1.0'];
			$urls[] = ['loc' => $base.'/documentation/', 'changefreq' => 'weekly', 'priority' => '0.8'];
			$urls[] = ['loc' => $base.'/articles/', 'changefreq' => 'weekly', 'priority' => '0.8'];
			$urls[] = ['loc' => $base.'/use/', 'changefreq' => 'weekly', 'priority' => '0.8'];
			$urls[] = ['loc' => $base.'/pricing/', 'changefreq' => 'weekly', 'priority' => '0.8'];
			$urls[] = ['loc' => $base.'/terms/', 'changefreq' => 'monthly', 'priority' => '0.3'];
			$urls[] = ['loc' => $base.'/privacy/', 'changefreq' => 'monthly', 'priority' => '0.3'];
			$urls[] = ['loc' => $base.'/dashboard/', 'changefreq' => 'weekly', 'priority' => '0.9'];
			$urls[] = ['loc' => $base.'/dashboard/auth/', 'changefreq' => 'weekly', 'priority' => '0.7'];
		} else {
			$urls[] = ['loc' => $base.'/', 'changefreq' => 'daily', 'priority' => '1.0'];
			$urls[] = ['loc' => $base.'/journal/', 'changefreq' => 'daily', 'priority' => '0.9'];
			$urls[] = ['loc' => $base.'/playbooks/', 'changefreq' => 'daily', 'priority' => '0.9'];
			$urls[] = ['loc' => $base.'/signals/', 'changefreq' => 'daily', 'priority' => '0.8'];
			$urls[] = ['loc' => $base.'/fun/', 'changefreq' => 'daily', 'priority' => '0.7'];
			$urls[] = ['loc' => $base.'/solutions/downloads/', 'changefreq' => 'weekly', 'priority' => '0.9'];
			$urls[] = ['loc' => $base.'/solutions/articles/', 'changefreq' => 'weekly', 'priority' => '0.9'];
			$urls[] = ['loc' => $base.'/services/', 'changefreq' => 'weekly', 'priority' => '0.9'];
			$urls[] = ['loc' => $base.'/projects/', 'changefreq' => 'weekly', 'priority' => '0.9'];
			$urls[] = ['loc' => $base.'/contact/', 'changefreq' => 'weekly', 'priority' => '0.8'];
			$urls[] = ['loc' => $base.'/audit/', 'changefreq' => 'weekly', 'priority' => '0.7'];
			$urls[] = ['loc' => $base.'/terms/', 'changefreq' => 'monthly', 'priority' => '0.3'];
			$urls[] = ['loc' => $base.'/privacy/', 'changefreq' => 'monthly', 'priority' => '0.3'];
		}

		if (!$isApiGeoOnlineSitemap) {
			$urls = array_merge($urls, $this->FetchSitemapBlogUrls($base));
			$urls = array_merge($urls, $this->FetchSitemapServiceUrls($base));
			$urls = array_merge($urls, $this->FetchSitemapProjectUrls($base));
			$urls = array_merge($urls, $this->FetchSitemapMirrorRouteUrls($base));
		}

		$dedupe = [];
		$normalized = [];
		foreach ($urls as $urlRow) {
			$loc = trim((string)($urlRow['loc'] ?? ''));
			if ($loc === '') {
				continue;
			}
			if (!isset($dedupe[$loc])) {
				$dedupe[$loc] = true;
				$normalized[] = [
					'loc' => $loc,
					'changefreq' => (string)($urlRow['changefreq'] ?? 'weekly'),
					'priority' => (string)($urlRow['priority'] ?? '0.5'),
				];
			}
		}
		$urls = $normalized;

		$now = gmdate('c');
		header('Content-Type: application/xml; charset=UTF-8');
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">'."\n";
		foreach ($urls as $url) {
			$loc = htmlspecialchars((string)$url['loc'], ENT_QUOTES, 'UTF-8');
			$freq = htmlspecialchars((string)$url['changefreq'], ENT_QUOTES, 'UTF-8');
			$priority = htmlspecialchars((string)$url['priority'], ENT_QUOTES, 'UTF-8');
			echo "  <url>\n";
			echo '    <loc>'.$loc."</loc>\n";
			echo '    <lastmod>'.$now."</lastmod>\n";
			echo '    <changefreq>'.$freq."</changefreq>\n";
			echo '    <priority>'.$priority."</priority>\n";
			echo "  </url>\n";
		}
		echo "</urlset>\n";
	}

	private function FetchSitemapBlogUrls(string $base): array {
		$result = [];
		if (!class_exists('FRMWRK')) {
			return $result;
		}
		try {
			$FRMWRK = new FRMWRK();
			$DB = $FRMWRK->DB();
			if (!$DB) {
				return $result;
			}
			$tableCheck = mysqli_query(
				$DB,
				"SELECT 1 FROM information_schema.TABLES
				 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'examples_articles' LIMIT 1"
			);
			if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
				return $result;
			}

			$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
			if (strpos($host, ':') !== false) {
				$host = explode(':', $host, 2)[0];
			}
			$host = trim($host, '.');
			$hostSafe = mysqli_real_escape_string($DB, $host);

			$hasLangColumnRes = mysqli_query(
				$DB,
				"SELECT 1 FROM information_schema.COLUMNS
				 WHERE TABLE_SCHEMA = DATABASE()
				   AND TABLE_NAME = 'examples_articles'
				   AND COLUMN_NAME = 'lang_code'
				 LIMIT 1"
			);
			$hasLangColumn = $hasLangColumnRes && mysqli_num_rows($hasLangColumnRes) > 0;
			$preferredLang = (preg_match('/\.ru$/', $host) === 1) ? 'ru' : 'en';
			$langCond = $hasLangColumn
				? (($preferredLang === 'ru') ? "AND lang_code = 'ru'" : "AND lang_code = 'en'")
				: "";

			$hasClusterColumnRes = mysqli_query(
				$DB,
				"SELECT 1 FROM information_schema.COLUMNS
				 WHERE TABLE_SCHEMA = DATABASE()
				   AND TABLE_NAME = 'examples_articles'
				   AND COLUMN_NAME = 'cluster_code'
				 LIMIT 1"
			);
			$hasClusterColumn = $hasClusterColumnRes && mysqli_num_rows($hasClusterColumnRes) > 0;

			$rows = $FRMWRK->DBRecords(
				"SELECT slug" . ($hasClusterColumn ? ", cluster_code" : "") . "
				 FROM examples_articles
				 WHERE is_published = 1
				   AND slug IS NOT NULL
				   AND slug <> ''
				   AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
				   {$langCond}
				 ORDER BY slug ASC, id DESC"
			);
			$seen = [];
			$seenClusters = [];
			foreach ($rows as $row) {
				$slug = trim((string)($row['slug'] ?? ''));
				if ($slug === '' || isset($seen[$slug])) {
					continue;
				}
				$seen[$slug] = true;
				$clusterCode = trim((string)($row['cluster_code'] ?? ''));
				if ($hasClusterColumn && $clusterCode !== '' && !isset($seenClusters[$clusterCode])) {
					$seenClusters[$clusterCode] = true;
					$result[] = [
						'loc' => $base . '/journal/' . rawurlencode($clusterCode) . '/',
						'changefreq' => 'weekly',
						'priority' => '0.6'
					];
				}
				$result[] = [
					'loc' => $base . ($clusterCode !== ''
						? '/journal/' . rawurlencode($clusterCode) . '/' . rawurlencode($slug) . '/'
						: '/journal/' . rawurlencode($slug) . '/'),
					'changefreq' => 'weekly',
					'priority' => '0.7'
				];
			}
		} catch (\Throwable $e) {
			return [];
		}
		return $result;
	}

	private function FetchSitemapServiceUrls(string $base): array {
		$result = [];
		if (!class_exists('FRMWRK')) {
			return $result;
		}
		try {
			$FRMWRK = new FRMWRK();
			$DB = $FRMWRK->DB();
			if (!$DB) {
				return $result;
			}
			$tableCheck = mysqli_query(
				$DB,
				"SELECT 1 FROM information_schema.TABLES
				 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'public_services' LIMIT 1"
			);
			if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
				return $result;
			}
			$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
			if (strpos($host, ':') !== false) {
				$host = explode(':', $host, 2)[0];
			}
			$host = trim($host, '.');
			$hostSafe = mysqli_real_escape_string($DB, $host);
			$lang = (preg_match('/\.ru$/', $host) === 1) ? 'ru' : 'en';
			$langSafe = mysqli_real_escape_string($DB, $lang);

			$rows = $FRMWRK->DBRecords(
				"SELECT slug
				 FROM public_services
				 WHERE is_published = 1
				   AND slug IS NOT NULL
				   AND slug <> ''
				   AND lang_code = '{$langSafe}'
				   AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
				 ORDER BY slug ASC, id DESC"
			);
			$seen = [];
			foreach ($rows as $row) {
				$slug = trim((string)($row['slug'] ?? ''));
				if ($slug === '' || isset($seen[$slug])) {
					continue;
				}
				$seen[$slug] = true;
				$result[] = [
					'loc' => $base . '/services/' . rawurlencode($slug) . '/',
					'changefreq' => 'weekly',
					'priority' => '0.7'
				];
			}
		} catch (\Throwable $e) {
			return [];
		}
		return $result;
	}

	private function FetchSitemapProjectUrls(string $base): array {
		$result = [];
		if (!class_exists('FRMWRK')) {
			return $result;
		}
		try {
			$FRMWRK = new FRMWRK();
			$DB = $FRMWRK->DB();
			if (!$DB) {
				return $result;
			}
			$tableCheck = mysqli_query(
				$DB,
				"SELECT 1 FROM information_schema.TABLES
				 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'public_projects' LIMIT 1"
			);
			if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
				return $result;
			}
			$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
			if (strpos($host, ':') !== false) {
				$host = explode(':', $host, 2)[0];
			}
			$host = trim($host, '.');
			$hostSafe = mysqli_real_escape_string($DB, $host);
			$lang = (preg_match('/\.ru$/', $host) === 1) ? 'ru' : 'en';
			$langSafe = mysqli_real_escape_string($DB, $lang);

			$rows = $FRMWRK->DBRecords(
				"SELECT slug, symbolic_code
				 FROM public_projects
				 WHERE is_published = 1
				   AND (COALESCE(symbolic_code, '') <> '' OR COALESCE(slug, '') <> '')
				   AND lang_code = '{$langSafe}'
				   AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
				 ORDER BY id DESC"
			);
			$seen = [];
			foreach ($rows as $row) {
				$code = trim((string)($row['symbolic_code'] ?? ''));
				if ($code === '') {
					$code = trim((string)($row['slug'] ?? ''));
				}
				if ($code === '' || isset($seen[$code])) {
					continue;
				}
				$seen[$code] = true;
				$result[] = [
					'loc' => $base . '/projects/' . rawurlencode($code) . '/',
					'changefreq' => 'weekly',
					'priority' => '0.7'
				];
			}
		} catch (\Throwable $e) {
			return [];
		}
		return $result;
	}

	private function FetchSitemapCaseUrls(string $base): array {
		$result = [];
		if (!class_exists('FRMWRK')) {
			return $result;
		}
		try {
			$FRMWRK = new FRMWRK();
			$DB = $FRMWRK->DB();
			if (!$DB) {
				return $result;
			}
			$tableCheck = mysqli_query(
				$DB,
				"SELECT 1 FROM information_schema.TABLES
				 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'public_cases' LIMIT 1"
			);
			if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
				return $result;
			}
			$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
			if (strpos($host, ':') !== false) {
				$host = explode(':', $host, 2)[0];
			}
			$host = trim($host, '.');
			$hostSafe = mysqli_real_escape_string($DB, $host);
			$lang = (preg_match('/\.ru$/', $host) === 1) ? 'ru' : 'en';
			$langSafe = mysqli_real_escape_string($DB, $lang);

			$rows = $FRMWRK->DBRecords(
				"SELECT slug, symbolic_code
				 FROM public_cases
				 WHERE is_published = 1
				   AND (COALESCE(symbolic_code, '') <> '' OR COALESCE(slug, '') <> '')
				   AND lang_code = '{$langSafe}'
				   AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
				 ORDER BY id DESC"
			);
			$seen = [];
			foreach ($rows as $row) {
				$code = trim((string)($row['symbolic_code'] ?? ''));
				if ($code === '') {
					$code = trim((string)($row['slug'] ?? ''));
				}
				if ($code === '' || isset($seen[$code])) {
					continue;
				}
				$seen[$code] = true;
				$result[] = [
					'loc' => $base . '/cases/' . rawurlencode($code) . '/',
					'changefreq' => 'weekly',
					'priority' => '0.7'
				];
			}

			$projectTableCheck = mysqli_query(
				$DB,
				"SELECT 1 FROM information_schema.TABLES
				 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'public_projects' LIMIT 1"
			);
			if ($projectTableCheck && mysqli_num_rows($projectTableCheck) > 0) {
				$projectRows = $FRMWRK->DBRecords(
					"SELECT slug, symbolic_code
					 FROM public_projects
					 WHERE is_published = 1
					   AND (COALESCE(symbolic_code, '') <> '' OR COALESCE(slug, '') <> '')
					   AND lang_code = '{$langSafe}'
					   AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
					 ORDER BY id DESC"
				);
				foreach ($projectRows as $projectRow) {
					$code = trim((string)($projectRow['symbolic_code'] ?? ''));
					if ($code === '') {
						$code = trim((string)($projectRow['slug'] ?? ''));
					}
					if ($code === '' || isset($seen[$code])) {
						continue;
					}
					$seen[$code] = true;
					$result[] = [
						'loc' => $base . '/cases/' . rawurlencode($code) . '/',
						'changefreq' => 'weekly',
						'priority' => '0.7'
					];
				}
			}
		} catch (\Throwable $e) {
			return [];
		}
		return $result;
	}

	private function FetchSitemapOfferUrls(string $base): array {
		$result = [];
		try {
			$controlsPath = rtrim((string)DIR, '/\\') . '/core/controls/offers.php';
			if (!is_file($controlsPath)) {
				return $result;
			}
			$raw = (string)@file_get_contents($controlsPath);
			if ($raw === '') {
				return $result;
			}
			$matches = [];
			if (!preg_match_all("/'slug'\\s*=>\\s*'([a-z0-9_-]+)'/i", $raw, $matches)) {
				return $result;
			}
			$seen = [];
			foreach ((array)($matches[1] ?? []) as $slugRaw) {
				$slug = strtolower(trim((string)$slugRaw));
				if ($slug === '' || isset($seen[$slug])) {
					continue;
				}
				$seen[$slug] = true;
				$result[] = [
					'loc' => $base . '/offers/' . rawurlencode($slug) . '/',
					'changefreq' => 'weekly',
					'priority' => '0.7'
				];
			}
		} catch (\Throwable $e) {
			return [];
		}
		return $result;
	}

	private function FetchSitemapMirrorRouteUrls(string $base): array {
		$result = [];
		if (!class_exists('FRMWRK')) {
			return $result;
		}
		try {
			$FRMWRK = new FRMWRK();
			$DB = $FRMWRK->DB();
			if (!$DB) {
				return $result;
			}
			$tableCheck = mysqli_query(
				$DB,
				"SELECT 1 FROM information_schema.TABLES
				 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'mirror_routes' LIMIT 1"
			);
			if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
				return $result;
			}
			$rows = $FRMWRK->DBRecords(
				"SELECT route_type, route_name, page_name
				 FROM mirror_routes
				 WHERE is_active = 1
				 ORDER BY sort_order ASC, id ASC"
			);
			$seen = [];
			foreach ($rows as $row) {
				$routeType = trim((string)($row['route_type'] ?? 'page'));
				$routeName = trim((string)($row['route_name'] ?? ''));
				$pageName = trim((string)($row['page_name'] ?? ''));
				if ($routeName === '') {
					continue;
				}
				$path = '';
				if ($routeType === 'section_page') {
					$path = ($pageName === '' || $pageName === 'main')
						? '/' . rawurlencode($routeName) . '/'
						: '/' . rawurlencode($routeName) . '/' . rawurlencode($pageName) . '/';
				} else {
					$path = '/' . rawurlencode($routeName) . '/';
				}
				if (isset($seen[$path])) {
					continue;
				}
				$seen[$path] = true;
				$result[] = [
					'loc' => $base . $path,
					'changefreq' => 'weekly',
					'priority' => '0.6'
				];
			}
		} catch (\Throwable $e) {
			return [];
		}
		return $result;
	}

	private function FetchSitemapExampleArticleUrls(string $base): array {
		$result = [];
		if (!class_exists('FRMWRK')) {
			return $result;
		}

		try {
			$FRMWRK = new FRMWRK();
			$DB = $FRMWRK->DB();
			if (!$DB) {
				return $result;
			}

			$tableCheck = mysqli_query(
				$DB,
				"SELECT 1
				 FROM information_schema.TABLES
				 WHERE TABLE_SCHEMA = DATABASE()
				   AND TABLE_NAME = 'examples_articles'
				 LIMIT 1"
			);
			if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
				return $result;
			}

			$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
			if (strpos($host, ':') !== false) {
				$host = explode(':', $host, 2)[0];
			}
			$host = trim($host, '.');
			$hostSafe = mysqli_real_escape_string($DB, $host);

			$hasLangColumnRes = mysqli_query(
				$DB,
				"SELECT 1
				 FROM information_schema.COLUMNS
				 WHERE TABLE_SCHEMA = DATABASE()
				   AND TABLE_NAME = 'examples_articles'
				   AND COLUMN_NAME = 'lang_code'
				 LIMIT 1"
			);
			$hasLangColumn = $hasLangColumnRes && mysqli_num_rows($hasLangColumnRes) > 0;
			$hasClusterColumnRes = mysqli_query(
				$DB,
				"SELECT 1
				 FROM information_schema.COLUMNS
				 WHERE TABLE_SCHEMA = DATABASE()
				   AND TABLE_NAME = 'examples_articles'
				   AND COLUMN_NAME = 'cluster_code'
				 LIMIT 1"
			);
			$hasClusterColumn = $hasClusterColumnRes && mysqli_num_rows($hasClusterColumnRes) > 0;

			$preferredLang = (preg_match('/\.ru$/', $host) === 1) ? 'ru' : 'en';
			if ($preferredLang === 'ru' && !$hasLangColumn) {
				return [];
			}
			$langCond = $hasLangColumn
				? (($preferredLang === 'ru') ? "AND lang_code = 'ru'" : "AND lang_code = 'en'")
				: "";
			$langOrder = $hasLangColumn
				? "ORDER BY slug ASC, id DESC"
				: "ORDER BY slug ASC, id DESC";

			$rows = $FRMWRK->DBRecords(
				"SELECT slug" . ($hasLangColumn ? ", lang_code" : "") . ($hasClusterColumn ? ", cluster_code" : "") . "
				 FROM examples_articles
				 WHERE is_published = 1
				   AND slug IS NOT NULL
				   AND slug <> ''
				   AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
				   {$langCond}
				 {$langOrder}"
			);

			$seen = [];
			$seenClusters = [];
			foreach ($rows as $row) {
				$slug = trim((string)($row['slug'] ?? ''));
				if ($slug === '' || isset($seen[$slug])) {
					continue;
				}
				$seen[$slug] = true;
				$isApiGeoOnlineHost = in_array($host, ['apigeoip.online', 'www.apigeoip.online'], true);
				$clusterCode = trim((string)($row['cluster_code'] ?? ''));
				if ($clusterCode !== '' && !isset($seenClusters[$clusterCode])) {
					$seenClusters[$clusterCode] = true;
					$result[] = [
						'loc' => $base . ($isApiGeoOnlineHost ? '/articles/' : '/journal/') . rawurlencode($clusterCode) . '/',
						'changefreq' => 'weekly',
						'priority' => '0.6'
					];
				}
				$articlePath = $isApiGeoOnlineHost
					? ($clusterCode !== '' ? '/articles/' . rawurlencode($clusterCode) . '/' . rawurlencode($slug) . '/' : '/articles/' . rawurlencode($slug) . '/')
					: ($clusterCode !== '' ? '/journal/' . rawurlencode($clusterCode) . '/' . rawurlencode($slug) . '/' : '/journal/' . rawurlencode($slug) . '/');
				$result[] = [
					'loc' => $base . $articlePath,
					'changefreq' => 'weekly',
					'priority' => '0.7'
				];
			}
		} catch (\Throwable $e) {
			return [];
		}

		return $result;
	}

	private function FetchSitemapToolUrls(string $base): array {
		$result = [];
		if (!class_exists('FRMWRK')) {
			return $result;
		}

		try {
			$FRMWRK = new FRMWRK();
			$DB = $FRMWRK->DB();
			if (!$DB) {
				return $result;
			}

			$tableCheck = mysqli_query(
				$DB,
				"SELECT 1
				 FROM information_schema.TABLES
				 WHERE TABLE_SCHEMA = DATABASE()
				   AND TABLE_NAME = 'public_tools'
				 LIMIT 1"
			);
			if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
				return $result;
			}

			$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
			if (strpos($host, ':') !== false) {
				$host = explode(':', $host, 2)[0];
			}
			$host = trim($host, '.');
			$hostSafe = mysqli_real_escape_string($DB, $host);

			$preferredLang = (preg_match('/\.ru$/', $host) === 1) ? 'ru' : 'en';
			$langSafe = mysqli_real_escape_string($DB, $preferredLang);
			$langCond = ($preferredLang === 'ru')
				? "lang_code IN ('ru', 'en')"
				: "lang_code = 'en'";

			$rows = $FRMWRK->DBRecords(
				"SELECT slug, lang_code
				 FROM public_tools
				 WHERE is_published = 1
				   AND slug IS NOT NULL
				   AND slug <> ''
				   AND (domain_host = '' OR domain_host = '{$hostSafe}')
				   AND {$langCond}
				 ORDER BY slug ASC, (lang_code = '{$langSafe}') DESC, id DESC"
			);

			$seen = [];
			foreach ($rows as $row) {
				$slug = trim((string)($row['slug'] ?? ''));
				if ($slug === '' || isset($seen[$slug])) {
					continue;
				}
				$seen[$slug] = true;
				$isApiGeoOnlineHost = in_array($host, ['apigeoip.online', 'www.apigeoip.online'], true);
				$toolsBasePath = $isApiGeoOnlineHost ? '/use/' : '/tools/';
				$result[] = [
					'loc' => $base . $toolsBasePath . rawurlencode($slug) . '/',
					'changefreq' => 'weekly',
					'priority' => '0.7'
				];
			}
		} catch (\Throwable $e) {
			return [];
		}

		return $result;
	}

	private function FetchSitemapBlogImageEntries(string $base): array {
		$result = [];
		if (!class_exists('FRMWRK')) {
			return $result;
		}
		try {
			$FRMWRK = new FRMWRK();
			$DB = $FRMWRK->DB();
			if (!$DB) {
				return $result;
			}
			$tableCheck = mysqli_query(
				$DB,
				"SELECT 1 FROM information_schema.TABLES
				 WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'examples_articles' LIMIT 1"
			);
			if (!$tableCheck || mysqli_num_rows($tableCheck) === 0) {
				return $result;
			}
			$hasLangColumnRes = mysqli_query(
				$DB,
				"SELECT 1 FROM information_schema.COLUMNS
				 WHERE TABLE_SCHEMA = DATABASE()
				   AND TABLE_NAME = 'examples_articles'
				   AND COLUMN_NAME = 'lang_code'
				 LIMIT 1"
			);
			$hasLangColumn = $hasLangColumnRes && mysqli_num_rows($hasLangColumnRes) > 0;
			$hasPreviewImageRes = mysqli_query(
				$DB,
				"SELECT 1 FROM information_schema.COLUMNS
				 WHERE TABLE_SCHEMA = DATABASE()
				   AND TABLE_NAME = 'examples_articles'
				   AND COLUMN_NAME = 'preview_image_url'
				 LIMIT 1"
			);
			$hasPreviewImage = $hasPreviewImageRes && mysqli_num_rows($hasPreviewImageRes) > 0;
			$hasPreviewThumbRes = mysqli_query(
				$DB,
				"SELECT 1 FROM information_schema.COLUMNS
				 WHERE TABLE_SCHEMA = DATABASE()
				   AND TABLE_NAME = 'examples_articles'
				   AND COLUMN_NAME = 'preview_image_thumb_url'
				 LIMIT 1"
			);
			$hasPreviewThumb = $hasPreviewThumbRes && mysqli_num_rows($hasPreviewThumbRes) > 0;
			$hasClusterColumnRes = mysqli_query(
				$DB,
				"SELECT 1 FROM information_schema.COLUMNS
				 WHERE TABLE_SCHEMA = DATABASE()
				   AND TABLE_NAME = 'examples_articles'
				   AND COLUMN_NAME = 'cluster_code'
				 LIMIT 1"
			);
			$hasClusterColumn = $hasClusterColumnRes && mysqli_num_rows($hasClusterColumnRes) > 0;
			if (!$hasPreviewImage && !$hasPreviewThumb) {
				return $result;
			}

			$host = strtolower((string)($_SERVER['MIRROR_DOMAIN_HOST'] ?? $_SERVER['HTTP_HOST'] ?? ''));
			if (strpos($host, ':') !== false) {
				$host = explode(':', $host, 2)[0];
			}
			$host = trim($host, '.');
			$hostSafe = mysqli_real_escape_string($DB, $host);
			$preferredLang = (preg_match('/\.ru$/', $host) === 1) ? 'ru' : 'en';
			$langCond = $hasLangColumn
				? (($preferredLang === 'ru') ? "AND lang_code = 'ru'" : "AND lang_code = 'en'")
				: "";
			$previewImageSelect = $hasPreviewImage ? "preview_image_url" : "'' AS preview_image_url";
			$previewThumbSelect = $hasPreviewThumb ? "preview_image_thumb_url" : "'' AS preview_image_thumb_url";

			$rows = $FRMWRK->DBRecords(
				"SELECT slug" . ($hasClusterColumn ? ", cluster_code" : "") . ", {$previewImageSelect}, {$previewThumbSelect}
				 FROM examples_articles
				 WHERE is_published = 1
				   AND slug IS NOT NULL
				   AND slug <> ''
				   AND (domain_host IS NULL OR domain_host = '' OR domain_host = '{$hostSafe}')
				   {$langCond}
				 ORDER BY id DESC"
			);

			$seen = [];
			foreach ($rows as $row) {
				$slug = trim((string)($row['slug'] ?? ''));
				if ($slug === '' || isset($seen[$slug])) {
					continue;
				}
				$seen[$slug] = true;
				$clusterCode = trim((string)($row['cluster_code'] ?? ''));
				$loc = $base
					. ($clusterCode !== ''
						? '/journal/' . rawurlencode($clusterCode) . '/' . rawurlencode($slug) . '/'
						: '/journal/' . rawurlencode($slug) . '/');
				$images = [];
				foreach (['preview_image_url', 'preview_image_thumb_url'] as $imgKey) {
					$raw = trim((string)($row[$imgKey] ?? ''));
					if ($raw === '') {
						continue;
					}
					$abs = $raw;
					if (strpos($abs, '//') === 0) {
						$abs = 'https:' . $abs;
					} elseif (!preg_match('#^https?://#i', $abs)) {
						if ($abs[0] === '/') {
							$abs = $base . $abs;
						} else {
							$abs = $base . '/' . ltrim($abs, '/');
						}
					}
					if (!in_array($abs, $images, true)) {
						$images[] = $abs;
					}
				}
				if (!empty($images)) {
					$result[] = ['loc' => $loc, 'images' => $images];
				}
			}
		} catch (\Throwable $e) {
			return [];
		}
		return $result;
	}

	private function RenderSitemapImagesXml(): void {
		while (ob_get_level() > 0) {
			ob_end_clean();
		}
		$base = $this->CurrentBaseUrl();
		$entries = $this->FetchSitemapBlogImageEntries($base);
		header('Content-Type: application/xml; charset=UTF-8');
		echo '<?xml version="1.0" encoding="UTF-8"?>'."\n";
		echo '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'."\n";
		foreach ($entries as $entry) {
			$loc = htmlspecialchars((string)($entry['loc'] ?? ''), ENT_QUOTES, 'UTF-8');
			if ($loc === '') {
				continue;
			}
			echo "  <url>\n";
			echo '    <loc>'.$loc."</loc>\n";
			foreach ((array)($entry['images'] ?? []) as $img) {
				$imgLoc = htmlspecialchars((string)$img, ENT_QUOTES, 'UTF-8');
				if ($imgLoc === '') {
					continue;
				}
				echo "    <image:image><image:loc>{$imgLoc}</image:loc></image:image>\n";
			}
			echo "  </url>\n";
		}
		echo "</urlset>\n";
	}

	private function RenderRobotsTxt(): void {
		while (ob_get_level() > 0) {
			ob_end_clean();
		}

		header('Content-Type: text/plain; charset=UTF-8');
		$base = $this->CurrentBaseUrl();
		echo "User-agent: *\n";
		echo "Allow: /\n";
		echo "Sitemap: {$base}/sitemap.xml\n";
		echo "Sitemap: {$base}/sitemap-images.xml\n";
	}
}

