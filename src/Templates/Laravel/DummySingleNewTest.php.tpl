
    public function testCustom()
    {
        $this->setUri('/*uri*/', '/*met*/', 200, [])->setAssertJson([])->getTest();
    }
}
