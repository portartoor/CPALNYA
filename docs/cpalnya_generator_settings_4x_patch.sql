SET @seo_patch = '{}';

UPDATE seo_generator_settings
SET settings_json = JSON_SET(
    settings_json,
    '$.daily_min', JSON_EXTRACT(@seo_patch, '$.daily_min'),
    '$.daily_max', JSON_EXTRACT(@seo_patch, '$.daily_max'),
    '$.max_per_run', JSON_EXTRACT(@seo_patch, '$.max_per_run'),
    '$.duplicate_retry_attempts', JSON_EXTRACT(@seo_patch, '$.duplicate_retry_attempts'),
    '$.word_min', JSON_EXTRACT(@seo_patch, '$.word_min'),
    '$.word_max', JSON_EXTRACT(@seo_patch, '$.word_max'),
    '$.today_first_delay_min', JSON_EXTRACT(@seo_patch, '$.today_first_delay_min'),
    '$.auto_expand_retries', JSON_EXTRACT(@seo_patch, '$.auto_expand_retries'),
    '$.expand_context_chars', JSON_EXTRACT(@seo_patch, '$.expand_context_chars'),
    '$.prompt_version', JSON_EXTRACT(@seo_patch, '$.prompt_version'),
    '$.narrative_person', JSON_EXTRACT(@seo_patch, '$.narrative_person'),
    '$.tone_variability', JSON_EXTRACT(@seo_patch, '$.tone_variability'),
    '$.topic_analysis_enabled', JSON_EXTRACT(@seo_patch, '$.topic_analysis_enabled'),
    '$.topic_analysis_limit', JSON_EXTRACT(@seo_patch, '$.topic_analysis_limit'),
    '$.topic_analysis_user_prompt_append', JSON_EXTRACT(@seo_patch, '$.topic_analysis_user_prompt_append'),
    '$.signals_news_enabled', JSON_EXTRACT(@seo_patch, '$.signals_news_enabled'),
    '$.signals_news_max_items', JSON_EXTRACT(@seo_patch, '$.signals_news_max_items'),
    '$.signals_news_lookback_hours', JSON_EXTRACT(@seo_patch, '$.signals_news_lookback_hours'),
    '$.signals_news_timeout', JSON_EXTRACT(@seo_patch, '$.signals_news_timeout'),
    '$.signals_news_feeds', JSON_EXTRACT(@seo_patch, '$.signals_news_feeds'),
    '$.styles_en', JSON_EXTRACT(@seo_patch, '$.styles_en'),
    '$.styles_ru', JSON_EXTRACT(@seo_patch, '$.styles_ru'),
    '$.clusters_en', JSON_EXTRACT(@seo_patch, '$.clusters_en'),
    '$.clusters_ru', JSON_EXTRACT(@seo_patch, '$.clusters_ru'),
    '$.intent_verticals_en', JSON_EXTRACT(@seo_patch, '$.intent_verticals_en'),
    '$.intent_verticals_ru', JSON_EXTRACT(@seo_patch, '$.intent_verticals_ru'),
    '$.intent_scenarios_en', JSON_EXTRACT(@seo_patch, '$.intent_scenarios_en'),
    '$.intent_scenarios_ru', JSON_EXTRACT(@seo_patch, '$.intent_scenarios_ru'),
    '$.intent_objectives_en', JSON_EXTRACT(@seo_patch, '$.intent_objectives_en'),
    '$.intent_objectives_ru', JSON_EXTRACT(@seo_patch, '$.intent_objectives_ru'),
    '$.intent_constraints_en', JSON_EXTRACT(@seo_patch, '$.intent_constraints_en'),
    '$.intent_constraints_ru', JSON_EXTRACT(@seo_patch, '$.intent_constraints_ru'),
    '$.intent_artifacts_en', JSON_EXTRACT(@seo_patch, '$.intent_artifacts_en'),
    '$.intent_artifacts_ru', JSON_EXTRACT(@seo_patch, '$.intent_artifacts_ru'),
    '$.intent_outcomes_en', JSON_EXTRACT(@seo_patch, '$.intent_outcomes_en'),
    '$.intent_outcomes_ru', JSON_EXTRACT(@seo_patch, '$.intent_outcomes_ru'),
    '$.service_focus_en', JSON_EXTRACT(@seo_patch, '$.service_focus_en'),
    '$.service_focus_ru', JSON_EXTRACT(@seo_patch, '$.service_focus_ru'),
    '$.forbidden_topics_en', JSON_EXTRACT(@seo_patch, '$.forbidden_topics_en'),
    '$.forbidden_topics_ru', JSON_EXTRACT(@seo_patch, '$.forbidden_topics_ru'),
    '$.article_structures_en', JSON_EXTRACT(@seo_patch, '$.article_structures_en'),
    '$.article_structures_ru', JSON_EXTRACT(@seo_patch, '$.article_structures_ru'),
    '$.moods', JSON_EXTRACT(@seo_patch, '$.moods'),
    '$.article_user_prompt_append_en', JSON_EXTRACT(@seo_patch, '$.article_user_prompt_append_en'),
    '$.article_user_prompt_append_ru', JSON_EXTRACT(@seo_patch, '$.article_user_prompt_append_ru'),
    '$.expand_user_prompt_append_en', JSON_EXTRACT(@seo_patch, '$.expand_user_prompt_append_en'),
    '$.expand_user_prompt_append_ru', JSON_EXTRACT(@seo_patch, '$.expand_user_prompt_append_ru'),
    '$.campaigns', JSON_EXTRACT(@seo_patch, '$.campaigns'),
    '$.article_cluster_taxonomy_en', JSON_EXTRACT(@seo_patch, '$.article_cluster_taxonomy_en'),
    '$.article_cluster_taxonomy_ru', JSON_EXTRACT(@seo_patch, '$.article_cluster_taxonomy_ru')
),
updated_at = NOW()
WHERE id = (
    SELECT id
    FROM (
        SELECT id
        FROM seo_generator_settings
        ORDER BY id DESC
        LIMIT 1
    ) t
);
