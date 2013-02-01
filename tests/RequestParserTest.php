<?php
namespace Ndm\JsonRpc2\Core;

/**
 *
 */
class RequestParserTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var \Ndm\JsonRpc2\Core\RequestParser
     */
    private $parser;

    public function setUp()
    {
        $this->parser = new \Ndm\JsonRpc2\Core\RequestParser();
    }

    public function tearDown()
    {
        unset ($this->parser);
    }

    /**
     * @return array
     */
    public static function validSingleRequestProvider()
    {
        return array(
            array('{"jsonrpc":"2.0","method":"foobar","params":[6],"id":1}', "foobar", array(6), true, 1,),
            array('{"jsonrpc":"2.0","method":"foobar","params":[6],"id":null}', "foobar", array(6), true, null),
            array('{"jsonrpc":"2.0","method":"subtract","params":[42,23],"id":1}', "subtract", array(42, 23), true, 1),
            array(
                '{"jsonrpc":"2.0","method":"subtract","params":{"subtrahend":23,"minuend":42},"id":3}',
                "subtract",
                (object)array("subtrahend" => 23, "minuend" => 42),
                true,
                3
            )
        );
    }

    /**
     * @param string $json
     * @param string $method
     * @param object|array|null $params
     * @param bool $hasId
     * @param null|int|string|float $id
     *
     * @dataProvider validSingleRequestProvider
     */
    public function testValidSingleRequest($json, $method, $params, $hasId, $id)
    {
        $request = $this->parser->parse($json);
        $expect = new Request($method, $params, $hasId, $id);
        $this->assertEquals($request, $expect);

    }

    /**
     * @param $json
     * @dataProvider validSingleRequestProvider
     */
    public function testValidSingleRequestRender($json)
    {
        $request = $this->parser->parse($json);
        $this->assertEquals($request->toJson(), $json);
    }

    /**
     * @return array
     */
    public static function requestValidIdProvider()
    {
        return array(
            array('{"jsonrpc": "2.0", "method": "foobar", "params": [6], "id": 1}', 1),
            array('{"jsonrpc": "2.0", "method": "foobar", "params": [6], "id": 1.1}', 1.1),
            // MESSAGES 'SHOULD NOT' CONTAIN fractional numerics
            array('{"jsonrpc": "2.0", "method": "foobar", "params": [6], "id": "abc"}', 'abc'),
            array('{"jsonrpc": "2.0", "method": "foobar", "params": [6], "id": null}', null),
            // MESSAGES 'SHOULD NOT' CONTAIN NULL ID's
        );
    }

    /**
     * Tests a selection of ID valid ID values- that are not notifications
     * @param $json
     * @param $id
     * @dataProvider requestValidIdProvider
     */
    public function testRequestValidId($json, $id)
    {
        $request = $this->parser->parse($json);
        $this->assertFalse($request->isNotification());
        $this->assertEquals($request->id, $id);
    }

    /**
     * @return array
     */
    public static function requestInvalidIdProvider()
    {
        return array(
            array('{"jsonrpc": "2.0", "method": "foobar", "params": [6], "id": []}'),
            array('{"jsonrpc": "2.0", "method": "foobar", "params": [6], "id": [1]}'),
            array('{"jsonrpc": "2.0", "method": "foobar", "params": [6], "id": {"abc":123}}'),
            array('{"jsonrpc": "2.0", "method": "foobar", "params": [6], "id": {}}'),
            array('{"jsonrpc": "2.0", "method": "foobar", "params": [6], "id": true}'),
        );
    }

    /**
     * @param $json
     * @dataProvider requestInvalidIdProvider
     */
    public function testRequestInvalidId($json)
    {
        $request = $this->parser->parse($json);
        $this->assertNull($request);
    }

    /**
     * @return array
     */
    public static function validNotificationProvider()
    {
        return array(
            array('{"jsonrpc": "2.0", "method": "foobar", "params": [6]}'),
        );
    }

    /**
     * Tests requests that are notifications- they must have no ID property
     * @param $json
     * @dataProvider validNotificationProvider
     */
    public function testValidNotification($json)
    {
        $request = $this->parser->parse($json);
        $this->assertTrue($request->isNotification());
    }

    /**
     * @return array
     */
    public static function validParamsProvider()
    {
        return array(
            array('{"jsonrpc": "2.0", "method": "foobar", "params": [6], "id": 1}', array(6)),
            array('{"jsonrpc": "2.0", "method": "foobar", "params": [], "id": 1}', array()),
            array('{"jsonrpc": "2.0", "method": "foobar", "params": {}, "id": 1}', (object)array()),
            array('{"jsonrpc": "2.0", "method": "foobar", "params": {"bar":6}, "id": 1}', (object)array("bar" => 6)),
            array(
                '{"jsonrpc": "2.0", "method": "foobar", "params": {"bar":["abc",1234]}, "id": 1}',
                (object)array("bar" => array("abc", 1234))
            ),
            array(
                '{"jsonrpc": "2.0", "method": "foobar", "params": {"bar":{"abc":1234}}, "id": 1}',
                (object)array("bar" => (object)array("abc" => 1234))
            ),
        );
    }


    /**
     * @param $json
     * @param $params
     * @dataProvider validParamsProvider
     */
    public function testValidParams($json, $params)
    {
        $request = $this->parser->parse($json);
        $this->assertEquals($request->params, $params);
    }

    /**
     *
     */
    public function testParamsOmitted()
    {
        $request = $this->parser->parse('{"jsonrpc": "2.0", "method": "foobar"}');
        $this->assertNull($request->params);
    }

    /**
     * @return array
     */
    public static function invalidParamsProvider()
    {
        return array(
            array('{"jsonrpc": "2.0", "method": "foobar", "params": null, "id": 1}'),
            array('{"jsonrpc": "2.0", "method": "foobar", "params": 1, "id": 1}'),
            array('{"jsonrpc": "2.0", "method": "foobar", "params": 1.1, "id": 1}'),
            array('{"jsonrpc": "2.0", "method": "foobar", "params": "", "id": 1}'),
            array('{"jsonrpc": "2.0", "method": "foobar", "params": "abc", "id": 1}'),
            array('{"jsonrpc": "2.0", "method": "foobar", "params": true, "id": 1}'),
        );
    }

    /**
     * @param $json
     * @dataProvider invalidParamsProvider
     */
    public function testInvalidParams($json)
    {
        $request = $this->parser->parse($json);
        $this->assertNull($request, 'Due to the value of the "params" the request should be invalid');
    }


    /**
     * @return array
     */
    public static function invalidSingleRequestProvider()
    {
        return array(
            array('4'),
            array('[]'),
            array('{}'),
            array('{"abc": 123}')
        );
    }

    /**
     * @param $json
     * @dataProvider invalidSingleRequestProvider
     */
    public function testInvalidSingleRequest($json)
    {
        $request = $this->parser->parse($json);
        $this->assertNull($request);
    }


    /**
     * @return array
     */
    public static function invalidJsonRequestProvider()
    {
        return array(
            array('{'),
            array('{]'),
            array('"4')
        );
    }

    /**
     * @expectedException \Ndm\JsonRpc2\Core\Exception\JsonParseException
     * @dataProvider invalidJsonRequestProvider
     */
    public function testInvalidJson($json)
    {
        $this->parser->parse($json);
    }


    /**
     *
     */
    public function testValidMethod()
    {
        $request = $this->parser->parse('{"jsonrpc": "2.0", "method": "foobar"}');
        $this->assertEquals($request->method, "foobar");
    }

    /**
     * @return array
     */
    public static function invalidMethodProvider()
    {
        return array(
            array('{"jsonrpc": "2.0", "method": ""}'),
            array('{"jsonrpc": "2.0", "method": 0 }'),
            array('{"jsonrpc": "2.0", "method": 1.1 }'),
            array('{"jsonrpc": "2.0", "method": true}'),
            array('{"jsonrpc": "2.0", "method": null}'),
            array('{"jsonrpc": "2.0", "method": []}'),
            array('{"jsonrpc": "2.0", "method": [1,2]}'),
            array('{"jsonrpc": "2.0", "method": {}}'),
            array('{"jsonrpc": "2.0", "method": {"abc":1234}}'),
            array('{"jsonrpc": "2.0", "method": {"abc":[1,2]}}'),
            array('{"jsonrpc": "2.0", "method": {"abc":{"def":"hgi"}}}'),
        );
    }

    /**
     * @param $json
     * @dataProvider invalidMethodProvider
     */
    public function testInvalidMethod($json)
    {
        $request = $this->parser->parse($json);
        $this->assertNull($request);
    }
}
