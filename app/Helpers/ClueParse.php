<?php


use Carbon\Carbon;

function parserJuLiangRequest($request)
{
    $tel = $request->get('telphone');

    if (!$tel)
        return null;

    $toDateTimeString = Carbon::parse((int)$request->get('create_time'))
        ->setTimezone(8)
        ->toDateTimeString();
    $noDispatch = $request->get('clue_type') === 2 ? 1 : 0;
    $adName = $request->get('promotion_name', $request->get('ad_name'));
    $adId = $request->get('promotion_id', $request->get('ad_id'));
    return [
        'source' => \App\Models\Clue::JU_LIANG,
        'name' => $request->get('name'),
        'phone' => $tel,
        'no_dispatch' => $noDispatch,
        'wechat' => $request->get('weixin'),
        'gender' => $request->get('gender'),
        'age' => $request->get('age'),
        'post_date' => $toDateTimeString,
        'url' => $request->get('external_url'),
        'site_name' => $request->get('site_name'),
        'ad_name' => $adName,
        'ad_id' => $adId,
        'type' => \App\Models\Clue::TYPE_TRANSFER,
        'location' => $request->get('location') ?: $request->get('tel_location'),
        'question_data' => $request->get('remark_dict'),
        'origin_data' => $request->all(),
        'uuid' => $request->get('id'),
    ];
}

function parserBaiduRequest($request)
{
    $tel = $request->get('cluePhoneNumber');
    if (!$tel)
        return null;

    $detail = $request->get('formDetail');
    if ($detail) {
        if (!is_array($detail))
            $detail = json_decode($detail, true);
        $arr = [];
        if (is_array($detail)) {
            foreach ($detail as $val) {
                if (is_array($val))
                    $arr[$val['name']] = $val['value'];
            }
        }
        $detail = $arr;
    }

    return [
        'source' => \App\Models\Clue::BAI_DU,
        'name' => $request->get('name'),
        'phone' => $tel,
        'ip' => $request->get('ip'),
        'wechat' => $request->get('weixin'),
        'gender' => $request->get('gender'),
        'age' => $request->get('age'),
        'post_date' => $request->get('commitTime'),
        'url' => $request->get('url'),
        'site_name' => $request->get('siteName'),
        'ad_name' => $request->get('planName'),
        'ad_id' => $request->get('planId'),
        'type' => \App\Models\Clue::TYPE_TRANSFER,
        'location' => $request->get('area'),
        'question_data' => $detail,
        'origin_data' => $request->all(),
        'uuid' => $request->get('clueId'),
    ];
}

function parserKuaiShouRequest($request)
{
    $tel = $request->get('phone');
    if (!$tel)
        return null;


    $detail = data_get($request->get('details'), 'form_details');
    if ($detail) {
        $arr = [];
        foreach ($detail as $val) {
            $arr[$val['key']] = $val['value'];
        }
        $detail = $arr;
    }


    return [
        'source' => \App\Models\Clue::KUAI_SHOU,
        'name' => $request->get('consumer_name'),
        'phone' => $tel,
        'ip' => $request->get('ip'),
        'wechat' => $request->get('we_chat'),
        'gender' => $request->get('gender'),
        'age' => $request->get('age'),
        'post_date' => $request->get('create_time_date_time'),
        'url' => $request->get('source_url'),
        'site_name' => $request->get('page_name'),
        'ad_name' => $request->get('campaign_name'),
        'ad_id' => $request->get('campaign_id'),
        'type' => \App\Models\Clue::TYPE_TRANSFER,
        'location' => $request->get('city_name'),
        'question_data' => $detail,
        'origin_data' => $request->all(),
        'uuid' => $request->get('clue_id'),
    ];
}

function parserTengXunRequest($request)
{
    $tel = $request->get('leads_tel');
    if (!$tel)
        return null;

    $detail = null;
    try {
        $val = json_decode($request->get('bundle'), true);
        if ($val)
            $detail = $val;
    } catch (\Exception $exception) {

    }
    if (!$detail)
        $detail = $request->get("layer_form_content");

    return [
        'source' => \App\Models\Clue::TENG_XUN,
        'name' => $request->get('leads_name'),
        'phone' => $tel,
        'ip' => $request->get('ip'),
        'wechat' => $request->get('leads_wechat'),
        'gender' => $request->get('leads_gender'),
        'age' => $request->get('age'),
        'post_date' => $request->get('leads_create_time'),
        'url' => $request->get('page_url'),
        'site_name' => $request->get('page_name'),
        'ad_name' => $request->get('campaign_name'),
        'ad_id' => $request->get('campaign_id'),
        'type' => \App\Models\Clue::TYPE_TRANSFER,
        'location' => $request->get('leads_area'),
        'question_data' => $detail,
        'origin_data' => $request->all(),
        'uuid' => $request->get('leads_id'),
    ];
}
