
    public function testCustom()
    {
        $this->master(
            [
                'method' => '/*met*/',
                'uri' => '/*uri*/',
                'data' => [

                    //custom_data

                ],
                'status' => 200,
                'user_id' => 2,
                'test_structure' => false,
                'structure' => [],

                'test_json' => true,
                'json' => [/*'custom_json'*/]


            ]
        );
    }
}
