<?php
    include_once "form_messages.php";

    $sheetId = '7454634898941828';
    $apiKey = '41q3jpge6xdr53oeh8qcypopra';
    $clinicField = 'Dantų tiesinimas be gėdos landing';
    $mailTo = 'gretajan09@gmail.com';
    $urlTo = 'https://api.smartsheet.com/2.0/sheets/' . $sheetId;
    $data = [];
    $errors = [];
    $input = file_get_contents('php://input');
    $inputs = JSON_decode($input);

    if(!$inputs)
    {
        return;
    } else {
        foreach($inputs as $key => $val)
        {
            $input_error = validateInput($key, $val);
            if($input_error)
            {
                array_push($errors, $input_error);
            }
        }

        if(count($errors) > 0)
        {
            $data = [
                'status' => 422,
                'errors' => $errors
            ];
            echo JSON_encode($data);
        } else {
            $data = createDataArr();
            echo JSON_encode(postData($data));
        }
    }

    function validateInput($key, $value)
    {
        global $empty_field;
        global $invalid_general_format;
        if($key === 'name' || $key === 'surname')
        {
            $error_item = null;
            if(empty($value))
            {
                $error_item = [
                    $key => $empty_field
                ];

            }
            return $error_item;
        } else if($key === 'email')
        {
            $error_item = null;
            if(empty($value))
            {
                $error_item = [
                    $key => $empty_field
                ];
            } else if(!filter_var($value, FILTER_VALIDATE_EMAIL))
            {
                $error_item = [
                    $key => $invalid_general_format
                ];
            }
            return $error_item;
        } else if($key === 'phone')
        {
            $error_item = null;
            if(empty($value))
            {
                $error_item = [
                    $key => $empty_field
                ];
               
            } else if(!preg_match("/^\+370 ?[0-9]{3} ?[0-9]{2} ?[0-9]{3}$|8 ?6 ?[0-9] ?[0-9] ?[0-9] ?[0-9] ?[0-9] ?[0-9] ?[0-9] ?$/", $value))
            {
                $error_item = [
                    $key => $invalid_general_format
                ];
            }
            return $error_item;
        }
    }

    function createDataArr(){
        global $urlTo; 
        global $apiKey;
        $ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_URL => $urlTo . '/columns',
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $apiKey,
				'Content-Type: application/json'
			),
		));
		$response = curl_exec($ch);
		if ($response === false) {
			return "CURL Error: " . curl_error($ch);
		}
        $sheetData = JSON_decode($response)->data;
        if($sheetData)
		    return createData($sheetData);
            else return false;
    }

    function createData($response){
        global $inputs; 
        global $clinicField;
        $fields = new stdClass();
        $fields->toTop = true;
        $fields->cells = [];
        $tempArray = [];
        foreach($response as $item)
        {
          
            if($item->type === 'TEXT_NUMBER')
            {
                $column_id = null;
                $column_value = null;
                $item_title = $item->title;
                if($item_title === 'IŠ KUR?')
                {
                    $column_id = $item->id;
                    $column_value =  $clinicField;
                } else if($item_title === 'VARDAS, PAVARDĖ')
                {
                    $column_id = $item->id;
                    $column_value =  $inputs->name . ' ' . $inputs->surname;
                } else if($item_title === 'TELEFONO NUMERIS')
                {
                    $column_id = $item->id;
                    $column_value =  $inputs->phone;
                }
                if($column_id)
                {
                    $array_item = [
                        'columnId' => $column_id,
                        'value' => $column_value
                    ];
                    array_push($tempArray, $array_item);
                }
            }
        }
        $fields->cells = $tempArray;
        return $fields;
    }

    function postData($data)
    {
        global $success_msg;
        global $urlTo;
        global $apiKey;
        $ch = curl_init();
        curl_setopt_array($ch, array(
			CURLOPT_URL => $urlTo . '/rows',
            CURLOPT_POST => TRUE,
			CURLOPT_RETURNTRANSFER => TRUE,
			CURLOPT_HTTPHEADER => array(
				'Authorization: Bearer ' . $apiKey,
				'Content-Type: application/json'
			),
            CURLOPT_POSTFIELDS => JSON_encode($data)
		));
        $server_output = curl_exec($ch);
        curl_close ($ch);
        if ($server_output == false) 
        {
            $data = [
                'status' => 500,
            ];
            return $data;
        } else {
            $data = [
                'status' => 200,
                'message' => $success_msg
            ];
            return $data;
        }
    }
