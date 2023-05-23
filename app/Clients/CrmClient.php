<?php

namespace App\Clients;

use App\Models\Clue;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CrmClient extends Request
{
    /**
     * @throws \Exception
     */
    public function __construct($data = null)
    {
        $data = $data ?: json_decode(admin_setting('crm_account'), true);

        if (!Arr::has($data, ['domain', 'username', 'password']))
            throw new \Exception("错误的信息,没法登录crm");

        $account = Arr::only($data, ['username', 'password']);
        $domain = Arr::get($data, 'domain');

        parent::__construct($account, $domain);

    }


    /**
     * 查看登录状态
     * @return bool
     * @throws GuzzleException
     */
    public function isLogin(): bool
    {
        $response = $this->get('/');
        $response = $response->getBody()->getContents();
        return !preg_match('/用户登录/', $response);
    }

    /**
     * 获取账号的登录状态
     * @return bool
     * @throws GuzzleException
     */
    public function loginStatus(): bool
    {
        if (!$this->isLogin()) {
            return $this->login();
        }
        return true;
    }

    /**
     * Crm登录方法
     * @return bool
     * @throws GuzzleException
     */
    public function login(): bool
    {
        $response = $this->post('/Account/Auth/Login', [
            'form_params' => $this->account,
        ]);
        $contents = $response->getBody()->getContents();
        $result = json_decode($contents, true);
        return data_get($result, 'statusCode', 0) === "200";
    }

    public function ReservationTempCustSearchIndex($data): \Illuminate\Support\Collection
    {
        $data = array_merge(ClientConstants::TEMP_API_BASE_PARAMS, $data);

        $response = $this->post('/Reservation/TempCustSearch/Index', [
            'form_params' => $data
        ]);
        $body = $response->getBody()->getContents();

        if (!preg_match("/建档时间/", $body))
            throw new \Exception('ReservationTempCustSearchIndex接口失败.');
        $t = parserHtmlTableToObject($body, '.table-striped', 'innerText');
        $result = collect($t)
            ->filter(function ($item) {
                return !!data_get($item, '媒介');
            });
        return $result;
    }

    public function FieldReceptionSearchIndex($data): \Illuminate\Support\Collection
    {
        $data = array_merge(ClientConstants::Field_ReceptionSearch_Index, $data);

        $response = $this->post('/Field/ReceptionSearch/Index', [
            'form_params' => $data
        ]);
        $body = $response->getBody()->getContents();

        if (!preg_match("/现场客服/", $body))
            throw new \Exception('FieldReceptionSearchIndex接口失败.');

        $t = parserHtmlTableToObject($body, '.table', 'innerText');
        $result = collect($t)
            ->filter(function ($item) {
                return !!data_get($item, '是否成交');
            });
        return $result;
    }


    public function ReservationTempCustInfoIndex($data): \Illuminate\Support\Collection
    {
        $data = array_merge([
            'pageCurrent' => 1,
        ], $data);
        $response = $this->get("/Reservation/TempCustInfo/Index", [
            'query' => $data
        ]);
        $body = $response->getBody()->getContents();

        if (!preg_match("/添加预约/", $body))
            throw new \Exception('ReservationTempCustInfoIndex接口失败.');
        $t = parserHtmlTableToObject($body, '.table', 'innerText');
        $result = collect($t)
            ->filter(function ($item) {
                return !!data_get($item, '建档时间');
            });
        return $result;
    }

    public function ReservationTempCustInfoCreateView()
    {
        $response = $this->get("Reservation/TempCustInfo/Create");
        return $response->getBody()->getContents();
    }

    public function ReservationTempCustInfoCreateApi($data)
    {
        $params = array_merge(ClientConstants::Reservation_TempCustInfo_Create_BASEDATA, $data);
//        $params['PlanRecallEmps'] = '王静';
        $response = $this->post("Reservation/TempCustInfo/Create", [
            'form_params' => $params
        ]);
        $body = $response->getBody()->getContents();
        $result = json_decode($body, true);
        $statusCode = data_get($result, 'statusCode');
        if (!$statusCode) {
            Log::debug("错误的Status 返回码", [$params]);
            return [
                'a_status' => Clue::A_STATUS_ARCHIVE_ERROR,
                'log' => "建档失败: 无法识别的结果",
                'crm_response' => $body,
            ];
        }

        if ($statusCode !== '200') {
            $message = data_get($result, 'message');
            if (preg_match("/(UQ_TEMP_CUST_INFO_ID_PHONE|电话已存在)/", $message)) {
                return [
                    'a_status' => Clue::A_STATUS_ARCHIVE_REPEAT,
                    'log' => '手机号码已建档',
                    'crm_response' => $body,
                ];
            } else {
                return [
                    'a_status' => Clue::A_STATUS_ARCHIVE_ERROR,
                    'log' => "建档失败: {$message}",
                    'crm_response' => $body,
                ];
            }
        }


        return [
            'a_status' => Clue::A_STATUS_ARCHIVE_SUCCESS,
            'log' => "建档成功",
            'crm_response' => $body,
        ];

    }

    public function getCustomerTypeList(): array
    {
        $html = $this->ReservationTempCustInfoCreateView();
        preg_match_all('/data-chid="(\w+)"[\n\t\w\-=" ]+[\t\n>]+(.*?)</', $html, $customerTypeList);
        $originData = data_get($customerTypeList, 0);
        if (!$originData) return [];

        $keyData = data_get($customerTypeList, 1);
        $nameData = data_get($customerTypeList, 2);
        $result = [];
        foreach ($originData as $index => $value) {
            if (!Str::contains($value, "data-issalon="))
                continue;
            $result[$keyData[$index]] = $nameData[$index];
        }
        return $result;
    }

    public function BaseDataManageCustMediaSourceIndex()
    {
        $response = $this->post('BaseDataManage/CustMediaSource/Index', [
            'form_params' => [
                "MediaCode" => "",
                "MediaName" => "",
                "MediaDesc" => "",
                "MediaTypeId" => "",
                "IsDisplay" => "Y",
                "pageSize" => "800",
                "pageCurrent" => "1",
                "orderField" => "",
                "orderDirection" => "",
                "total" => "",
            ]
        ]);
        return $response->getBody()->getContents();
    }

    public function getMediaTypeList(): array
    {
        $html = $this->BaseDataManageCustMediaSourceIndex();
        preg_match_all('/option value="([\dA-Z]{10,})">(.*?)</', $html, $parentList);
        $originData = data_get($parentList, 0);
        $parentResult = [];

        if ($originData) {
            $keyData = data_get($parentList, 1);
            $nameData = data_get($parentList, 2);

            $t = parserHtmlTableToObject($html, '.table', 'innerText');
            $result = collect($t)
                ->map(function ($item) {
                    return [
                        'key' => $item['value'],
                        'name' => $item['媒介名称'],
                        'parent_name' => $item['媒介类型']
                    ];
                });
            foreach ($originData as $index => $value) {
                $name = $nameData[$index];
                $parentResult[] = [
                    'key' => $keyData[$index],
                    'name' => $name,
                    'children' => $result->filter(function ($item) use ($name) {
                        return $item['parent_name'] === $name;
                    })->toArray()
                ];
            }
        }
        return $parentResult;
    }

    public function getUserIdOfCode($code): array
    {
        $html = $this->ReservationTempCustInfoCreateView();
        preg_match('/value="([\dA-Z]{10,})"[\t\n>]+(.+\(' . $code . '\))/', $html, $userData);
        return [
            'id' => data_get($userData, 1),
            'name' => data_get($userData, 2),
        ];
    }

    public static function make(): static
    {
        return new static();
    }

    public static function checkPhoneIsArchive($phone): bool
    {
        $result = static::make()->ReservationTempCustInfoIndex([
            "Phone" => $phone
        ]);
        Log::debug("查询CRM预约单是否创建 $phone", [$result]);
        return !!count($result);
    }

    /**
     * @throws \Exception
     */
    public static function test()
    {
        $client = new CrmClient([
            'username' => '7023',
            'password' => 'hm2018',
        ], 'zx');

        $item = $client->ReservationTempCustInfoIndex([
            'Phone' => '18792629031'
        ]);
        dd($item);
    }

    public static function getView1Data()
    {
        $client = new static([
            'domain' => 'http://172.16.8.1/',
            'username' => '999',
            'password' => 'wmx@1',
        ]);

        $date = now()->toDateString();
        $result = $client->FieldReceptionSearchIndex([
            "DatetimeReceptionStart" => $date,
            "DatetimeReceptionEnd" => $date,
        ]);
        $data = $result->filter(function ($item) {
            return $item['现场客服'] !== '公共池';
        })->sortBy("接待时间")
            ->unique("客户卡号")
            ->groupBy("现场客服");
        $result = [];
        foreach ($data as $key => $value) {
            $arr = $value->map(function ($item) {
                return [
                    'name' => $item['姓名'],
                    'type' => data_get($item,'预约类型') ?: "自然到院",
                ];
            });
            $result[] = [
                'owner' => $key,
                'count' => $arr->count(),
                'customers' => $arr->toArray(),
            ];
        }

        return $result;
    }

}
