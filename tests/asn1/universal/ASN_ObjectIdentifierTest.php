<?php
/*
 * This file is part of PHPASN1 written by Friedrich Große.
 * 
 * Copyright © Friedrich Große, Berlin 2012
 * 
 * PHPASN1 is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * PHPASN1 is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with PHPASN1.  If not, see <http://www.gnu.org/licenses/>.
 */
 
namespace PHPASN1;

require_once(dirname(__FILE__) . '/../../PHPASN1TestCase.class.php');
 
class ASN_ObjectIdentifierTest extends PHPASN1TestCase {
    
    /**
     * @expectedException \PHPASN1\GeneralException
     * @expectedExceptionMessage [1.Foo.3] is no valid object identifier (sub identifier 2 is not numeric)!     
     */
    public function testCreateWithInvalidObjectIdentifier() {
        new ASN_ObjectIdentifier('1.Foo.3');
    }
    
    public function testGetType() {
        $object = new ASN_ObjectIdentifier('1.2.3');
        $this->assertEquals(Identifier::OBJECT_IDENTIFIER, $object->getType());
    }
    
    public function testContent() {
        $object = new ASN_ObjectIdentifier('1.2.3');
        $this->assertEquals('1.2.3', $object->getContent());
    }
    
    public function testGetObjectLength() {
        $object = new ASN_ObjectIdentifier('1.2.3');
        $this->assertEquals(4, $object->getObjectLength());
        
        $object = new ASN_ObjectIdentifier('1.2.250.1.16.9');
        $this->assertEquals(8, $object->getObjectLength());
    }
    
    public function testGetBinary() {
        $object = new ASN_ObjectIdentifier('1.2.3');
        $expectedType     = chr(Identifier::OBJECT_IDENTIFIER);
        $expectedLength   = chr(0x02);
        $expectedContent  = chr(1 * 40 + 2);
        $expectedContent .= chr(3);
        $this->assertEquals($expectedType.$expectedLength.$expectedContent, $object->getBinary());
        
        $object = new ASN_ObjectIdentifier('1.2.250.1.16.9');
        $expectedLength   = chr(0x06);
        $expectedContent  = chr(1 * 40 + 2); // 1.2
        $expectedContent .= chr(128 | 1);    // 250
        $expectedContent .= chr(122);        // 
        $expectedContent .= chr(1);          //   1
        $expectedContent .= chr(16);         //  16
        $expectedContent .= chr(9);          //   9
        $this->assertEquals($expectedType.$expectedLength.$expectedContent, $object->getBinary());
    }
    
    /**
     * @depends testGetBinary
     */
    public function testFromBinary() {        
        $originalobject = new ASN_ObjectIdentifier('1.2.250.1.16.9');
        $binaryData = $originalobject->getBinary();
        $parsedObject = ASN_ObjectIdentifier::fromBinary($binaryData);
        $this->assertEquals($originalobject, $parsedObject);
    }
    
    /**
     * @depends testFromBinary
     */
    public function testFromBinaryWithOffset() {
        $originalobject1 = new ASN_ObjectIdentifier('1.2.3');
        $originalobject2 = new ASN_ObjectIdentifier('1.2.250.1.16.9');
        
        $binaryData  = $originalobject1->getBinary();
        $binaryData .= $originalobject2->getBinary();
        
        $offset = 0;        
        $parsedObject = ASN_ObjectIdentifier::fromBinary($binaryData, $offset);
        $this->assertEquals($originalobject1, $parsedObject);
        $this->assertEquals(4, $offset);
        $parsedObject = ASN_ObjectIdentifier::fromBinary($binaryData, $offset);
        $this->assertEquals($originalobject2, $parsedObject);
        $this->assertEquals(12, $offset);
    }
    
     /**
     * @expectedException PHPASN1\ASN1ParserException
     * @expectedExceptionMessage ASN.1 Parser Exception at offset 4: Malformed ASN.1 Object Identifier
     * @depends testFromBinary
     */
    public function testFromBinaryWithMalformedOID() {
        $binaryData  = chr(Identifier::OBJECT_IDENTIFIER);        
        $binaryData .= chr(0x03);
        $binaryData .= chr(42);
        $binaryData .= chr(128 | 1);
        $binaryData .= chr(128 | 1);
        ASN_ObjectIdentifier::fromBinary($binaryData);
    } 
    
}
    