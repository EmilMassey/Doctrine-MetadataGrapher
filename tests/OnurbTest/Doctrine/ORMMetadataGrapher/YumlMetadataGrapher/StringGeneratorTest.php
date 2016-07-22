<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */
namespace OnurbTest\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher;

use Doctrine\Common\Persistence\Mapping\ClassMetadata;
use Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\ClassStore;
use Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\StringGenerator;
use PHPUnit_Framework_TestCase;

/**
 * Tests for the metadata to string converter
 *
 * @license MIT
 * @link    http://www.doctrine-project.org/
 * @author  Marco Pivetta <ocramius@gmail.com>
 * @author  Bruno Heron <herobrun@gmail.com>
 */
class StringGeneratorTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var StringGenerator
     */
    protected $stringGenerator;

    public function testInstance()
    {
        $classStore = $this->getMock('Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\ClassStoreInterface');
        $stringGenerator = new StringGenerator($classStore);

        $this->assertInstanceOf(
            'Onurb\\Doctrine\\ORMMetadataGrapher\\YumlMetadataGrapher\\StringGeneratorInterface',
            $stringGenerator
        );
    }

    public function testGetAssociationLogger()
    {
        $classStore = $this->getMock('Onurb\Doctrine\ORMMetadataGrapher\YumlMetadataGrapher\ClassStoreInterface');
        $stringGenerator = new StringGenerator($classStore);

        $this->assertInstanceOf(
            'Onurb\\Doctrine\\ORMMetadataGrapher\\YumlMetadataGrapher'
            . '\\StringGenerator\\VisitedAssociationLoggerInterface',
            $stringGenerator->getAssociationLogger()
        );
    }

    public function testGetClassStringClass()
    {
        $class1 = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');
        $class1->expects($this->any())->method('getName')->will($this->returnValue('Extended\\Entity'));
        $class1->expects($this->any())->method('getFieldNames')->will($this->returnValue(array('a', 'b', 'c')));
        $class1->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array()));
        $class1->expects($this->any())->method('isIdentifier')->will(
            $this->returnCallback(
                function ($field) {
                    return $field === 'a';
                }
            )
        );

        $classParent = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');
        $classParent->expects($this->any())->method('getName')->will($this->returnValue('Parent\\Entity'));
        $classParent->expects($this->any())->method('getFieldNames')->will($this->returnValue(array('b')));
        $classParent->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array()));
        $classParent->expects($this->any())->method('isIdentifier')->will($this->returnValue(false));

        $classStore =
            $this->getMockBuilder('Onurb\\Doctrine\\ORMMetadataGrapher\\YumlMetadataGrapher\\ClassStoreInterface')
                ->getMock();

        $classStore->expects($this->any())->method('getParent')
            ->with($this->logicalOr($class1, $classParent))
            ->will($this->returnCallback(
                function ($class) use ($class1, $classParent) {
                    return $class == $class1 ? $classParent : null;
                }
            ));


        $stringGenerator = new StringGenerator($classStore);

        $this->assertSame('[Extended.Entity|+a;c]', $stringGenerator->getClassString($class1));
    }

    public function testGetClassStringWithParentFieldMatching()
    {
        $class1 = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');
        $class1->expects($this->any())->method('getName')->will($this->returnValue('Simple\\Entity'));
        $class1->expects($this->any())->method('getFieldNames')->will($this->returnValue(array('a', 'b', 'c')));
        $class1->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array()));
        $class1->expects($this->any())->method('isIdentifier')->will(
            $this->returnCallback(
                function ($field) {
                    return $field === 'a';
                }
            )
        );
    }

    /**
     * @covers \Onurb\Doctrine\ORMMetadataGrapher\YUMLMetadataGrapher\StringGenerator
     */
    public function testGetAssociationString()
    {
        $class1 = $this->getMockBuilder('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata')
            ->setMethods(array(
                'getName',
                'getIdentifier',
                'getReflectionClass',
                'isIdentifier',
                'hasField',
                'hasAssociation',
                'isSingleValuedAssociation',
                'isCollectionValuedAssociation',
                'getFieldNames',
                'getIdentifierFieldNames',
                'getAssociationNames',
                'getTypeOfField',
                'getAssociationTargetClass',
                'isAssociationInverseSide',
                'getAssociationMappedByTargetField',
                'getIdentifierValues',
                'getAssociationMapping',
            ))->getMock();

        $class1->expects($this->any())->method('getName')->will($this->returnValue('A'));
        $class1->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array('b')));
        $class1->expects($this->any())->method('getAssociationTargetClass')->will($this->returnValue('B'));
        $class1->expects($this->any())->method('getAssociationMapping')->will($this->returnValue(array(
            'isOwningSide' => true,
            'mappedBy'      => null,
            'inversedBy'    =>null
        )));

        $class1->expects($this->any())->method('isAssociationInverseSide')->will($this->returnValue(false));
        $class1->expects($this->any())->method('isCollectionValuedAssociation')->will($this->returnValue(false));
        $class1->expects($this->any())->method('getFieldNames')->will($this->returnValue(array()));

        $class2 = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');
        $class2->expects($this->any())->method('getName')->will($this->returnValue('B'));
        $class2->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array()));
        $class2->expects($this->any())->method('getFieldNames')->will($this->returnValue(array()));

        $classStore = new classStore(array($class1, $class2));

        $stringGenerator = new StringGenerator($classStore);

        $this->assertSame('[A]-b 1>[B]', $stringGenerator->getAssociationString($class1, 'b'));
    }

    /**
     * @covers \Onurb\Doctrine\ORMMetadataGrapher\YUMLMetadataGrapher\StringGenerator
     */
    public function testGetAssociationStringWithUnknownTargetClass()
    {
        $class1 = $this->getMockBuilder('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata')
            ->setMethods(array(
                'getName',
                'getIdentifier',
                'getReflectionClass',
                'isIdentifier',
                'hasField',
                'hasAssociation',
                'isSingleValuedAssociation',
                'isCollectionValuedAssociation',
                'getFieldNames',
                'getIdentifierFieldNames',
                'getAssociationNames',
                'getTypeOfField',
                'getAssociationTargetClass',
                'isAssociationInverseSide',
                'getAssociationMappedByTargetField',
                'getIdentifierValues',
                'getAssociationMapping',
            ))->getMock();

        $class1->expects($this->any())->method('getName')->will($this->returnValue('A'));
        $class1->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array('b', 'c')));
        $class1->expects($this->any())->method('getAssociationTargetClass')->will($this->returnCallback(
            function ($target) {
                return strtoupper($target);
            }
        ));
        $class1->expects($this->any())->method('getAssociationMapping')->will($this->returnValue(array(
            'isOwningSide' => true,
            'mappedBy'      => null,
            'inversedBy'    =>null
        )));

        $class1->expects($this->any())->method('isAssociationInverseSide')->will($this->returnValue(false));
        $class1->expects($this->any())->method('isCollectionValuedAssociation')->will($this->returnValue(false));
        $class1->expects($this->any())->method('getFieldNames')->will($this->returnValue(array()));

        $class2 = $this->getMock('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata');
        $class2->expects($this->any())->method('getName')->will($this->returnValue('B'));
        $class2->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array()));
        $class2->expects($this->any())->method('getFieldNames')->will($this->returnValue(array()));

        $classStore = new classStore(array($class1, $class2));

        $stringGenerator = new StringGenerator($classStore);

        $this->assertSame('[A]-b 1>[B]', $stringGenerator->getAssociationString($class1, 'b'));
        $this->assertSame('[A]<>-c 1>[C]', $stringGenerator->getAssociationString($class1, 'c'));
    }

    /**
     * @covers \Onurb\Doctrine\ORMMetadataGrapher\YUMLMetadataGrapher\StringGenerator
     */
    public function testGetAssociationStringWithUnknownTargetClassInverseSide()
    {
        $class1 = $this->getMockBuilder('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata')
            ->setMethods(array(
                'getName',
                'getIdentifier',
                'getReflectionClass',
                'isIdentifier',
                'hasField',
                'hasAssociation',
                'isSingleValuedAssociation',
                'isCollectionValuedAssociation',
                'getFieldNames',
                'getIdentifierFieldNames',
                'getAssociationNames',
                'getTypeOfField',
                'getAssociationTargetClass',
                'isAssociationInverseSide',
                'getAssociationMappedByTargetField',
                'getIdentifierValues',
                'getAssociationMapping',
            ))->getMock();

        $class1->expects($this->any())->method('getName')->will($this->returnValue('A'));
        $class1->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array('b')));
        $class1->expects($this->any())->method('getAssociationTargetClass')->will($this->returnCallback(
            function ($target) {
                return strtoupper($target);
            }
        ));
        $class1->expects($this->any())->method('getAssociationMapping')->will($this->returnValue(array(
            'isOwningSide' => false,
            'mappedBy'      => null,
            'inversedBy'    =>null
        )));

        $class1->expects($this->any())->method('isAssociationInverseSide')->will($this->returnValue(true));
        $class1->expects($this->any())->method('isCollectionValuedAssociation')->will($this->returnValue(false));
        $class1->expects($this->any())->method('getFieldNames')->will($this->returnValue(array()));


        $class2 = $this->getMockBuilder('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata')
            ->setMethods(array(
                'getName',
                'getIdentifier',
                'getReflectionClass',
                'isIdentifier',
                'hasField',
                'hasAssociation',
                'isSingleValuedAssociation',
                'isCollectionValuedAssociation',
                'getFieldNames',
                'getIdentifierFieldNames',
                'getAssociationNames',
                'getTypeOfField',
                'getAssociationTargetClass',
                'isAssociationInverseSide',
                'getAssociationMappedByTargetField',
                'getIdentifierValues',
                'getAssociationMapping',
            ))->getMock();

        $class2->expects($this->any())->method('getName')->will($this->returnValue('C'));
        $class2->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array('d')));
        $class2->expects($this->any())->method('getAssociationTargetClass')->will($this->returnCallback(
            function ($target) {
                return strtoupper($target);
            }
        ));
        $class2->expects($this->any())->method('getAssociationMapping')->will($this->returnValue(array(
            'isOwningSide' => true,
            'mappedBy'      => null,
            'inversedBy'    =>null
        )));

        $class2->expects($this->any())->method('isAssociationInverseSide')->will($this->returnValue(false));
        $class2->expects($this->any())->method('isCollectionValuedAssociation')->will($this->returnValue(true));
        $class2->expects($this->any())->method('getFieldNames')->will($this->returnValue(array()));

        $classStore = new classStore(array($class1, $class2));
        $stringGenerator = new StringGenerator($classStore);

        $this->assertSame('[A]<-b 1<>[B]', $stringGenerator->getAssociationString($class1, 'b'));
        $this->assertSame('[C]<>-d *>[D]', $stringGenerator->getAssociationString($class2, 'd'));
    }

    public function testGetAssociationMappingWithBidirectionnalOneToOneRelation()
    {
        $class1 = $this->getMockBuilder('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata')
            ->setMethods(array(
                'getName',
                'getIdentifier',
                'getReflectionClass',
                'isIdentifier',
                'hasField',
                'hasAssociation',
                'isSingleValuedAssociation',
                'isCollectionValuedAssociation',
                'getFieldNames',
                'getIdentifierFieldNames',
                'getAssociationNames',
                'getTypeOfField',
                'getAssociationTargetClass',
                'isAssociationInverseSide',
                'getAssociationMappedByTargetField',
                'getIdentifierValues',
                'getAssociationMapping',
            ))->getMock();

        $class1->expects($this->any())->method('getName')->will($this->returnValue('A'));
        $class1->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array('b')));
        $class1->expects($this->any())->method('getAssociationTargetClass')->will($this->returnCallback(
            function ($target) {
                return strtoupper($target);
            }
        ));
        $class1->expects($this->any())->method('getAssociationMapping')->will($this->returnValue(array(
            'isOwningSide' => true,
            'mappedBy'      => null,
            'inversedBy'    =>'a'
        )));
        $class1->expects($this->any())->method('isAssociationInverseSide')->will($this->returnValue(false));
        $class1->expects($this->any())->method('isCollectionValuedAssociation')->will($this->returnValue(false));
        $class1->expects($this->any())->method('getFieldNames')->will($this->returnValue(array()));

        $class2 = $this->getMockBuilder('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata')
            ->setMethods(array(
                'getName',
                'getIdentifier',
                'getReflectionClass',
                'isIdentifier',
                'hasField',
                'hasAssociation',
                'isSingleValuedAssociation',
                'isCollectionValuedAssociation',
                'getFieldNames',
                'getIdentifierFieldNames',
                'getAssociationNames',
                'getTypeOfField',
                'getAssociationTargetClass',
                'isAssociationInverseSide',
                'getAssociationMappedByTargetField',
                'getIdentifierValues',
                'getAssociationMapping',
            ))->getMock();

        $class2->expects($this->any())->method('getName')->will($this->returnValue('B'));
        $class2->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array('a')));
        $class2->expects($this->any())->method('getAssociationTargetClass')->will($this->returnCallback(
            function ($target) {
                return strtoupper($target);
            }
        ));
        $class2->expects($this->any())->method('getAssociationMapping')->will($this->returnValue(array(
            'isOwningSide' => false,
            'mappedBy'      => 'b',
            'inversedBy'    => null
        )));
        $class2->expects($this->any())->method('isAssociationInverseSide')->will($this->returnValue(true));
        $class2->expects($this->any())->method('isCollectionValuedAssociation')->will($this->returnValue(false));
        $class2->expects($this->any())->method('getFieldNames')->will($this->returnValue(array()));

        $classStore = new classStore(array($class1, $class2));
        $stringGenerator = new StringGenerator($classStore);

        $this->assertSame('[A]<>a 1-b 1>[B]', $stringGenerator->getAssociationString($class1, 'b'));
        $this->assertSame('[B]<b 1-a 1<>[A]', $stringGenerator->getAssociationString($class2, 'a'));
    }

    public function testGetAssociationMappingWithBidirectionnalManyToOneRelation()
    {
        $class1 = $this->getMockBuilder('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata')
            ->setMethods(array(
                'getName',
                'getIdentifier',
                'getReflectionClass',
                'isIdentifier',
                'hasField',
                'hasAssociation',
                'isSingleValuedAssociation',
                'isCollectionValuedAssociation',
                'getFieldNames',
                'getIdentifierFieldNames',
                'getAssociationNames',
                'getTypeOfField',
                'getAssociationTargetClass',
                'isAssociationInverseSide',
                'getAssociationMappedByTargetField',
                'getIdentifierValues',
                'getAssociationMapping',
            ))->getMock();

        $class1->expects($this->any())->method('getName')->will($this->returnValue('A'));
        $class1->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array('b')));
        $class1->expects($this->any())->method('getAssociationTargetClass')->will($this->returnCallback(
            function ($target) {
                return strtoupper($target);
            }
        ));
        $class1->expects($this->any())->method('getAssociationMapping')->will($this->returnValue(array(
            'isOwningSide' => true,
            'mappedBy'      => null,
            'inversedBy'    =>'a'
        )));
        $class1->expects($this->any())->method('isAssociationInverseSide')->will($this->returnValue(false));
        $class1->expects($this->any())->method('isCollectionValuedAssociation')->will($this->returnValue(true));
        $class1->expects($this->any())->method('getFieldNames')->will($this->returnValue(array()));

        $class2 = $this->getMockBuilder('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata')
            ->setMethods(array(
                'getName',
                'getIdentifier',
                'getReflectionClass',
                'isIdentifier',
                'hasField',
                'hasAssociation',
                'isSingleValuedAssociation',
                'isCollectionValuedAssociation',
                'getFieldNames',
                'getIdentifierFieldNames',
                'getAssociationNames',
                'getTypeOfField',
                'getAssociationTargetClass',
                'isAssociationInverseSide',
                'getAssociationMappedByTargetField',
                'getIdentifierValues',
                'getAssociationMapping',
            ))->getMock();

        $class2->expects($this->any())->method('getName')->will($this->returnValue('B'));
        $class2->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array('a')));
        $class2->expects($this->any())->method('getAssociationTargetClass')->will($this->returnCallback(
            function ($target) {
                return strtoupper($target);
            }
        ));
        $class2->expects($this->any())->method('getAssociationMapping')->will($this->returnValue(array(
            'isOwningSide' => false,
            'mappedBy'      => 'b',
            'inversedBy'    => null
        )));
        $class2->expects($this->any())->method('isAssociationInverseSide')->will($this->returnValue(true));
        $class2->expects($this->any())->method('isCollectionValuedAssociation')->will($this->returnValue(false));
        $class2->expects($this->any())->method('getFieldNames')->will($this->returnValue(array()));

        $classStore = new classStore(array($class1, $class2));
        $stringGenerator = new StringGenerator($classStore);

        $this->assertSame('[A]<>a 1-b *>[B]', $stringGenerator->getAssociationString($class1, 'b'));
        $this->assertSame('[B]<b *-a 1<>[A]', $stringGenerator->getAssociationString($class2, 'a'));
    }

    public function testGetAssociationMappingWithBidirectionnalManyToManyRelation()
    {
        $class1 = $this->getMockBuilder('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata')
            ->setMethods(array(
                'getName',
                'getIdentifier',
                'getReflectionClass',
                'isIdentifier',
                'hasField',
                'hasAssociation',
                'isSingleValuedAssociation',
                'isCollectionValuedAssociation',
                'getFieldNames',
                'getIdentifierFieldNames',
                'getAssociationNames',
                'getTypeOfField',
                'getAssociationTargetClass',
                'isAssociationInverseSide',
                'getAssociationMappedByTargetField',
                'getIdentifierValues',
                'getAssociationMapping',
            ))->getMock();

        $class1->expects($this->any())->method('getName')->will($this->returnValue('A'));
        $class1->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array('b')));
        $class1->expects($this->any())->method('getAssociationTargetClass')->will($this->returnCallback(
            function ($target) {
                return strtoupper($target);
            }
        ));
        $class1->expects($this->any())->method('getAssociationMapping')->will($this->returnValue(array(
            'isOwningSide' => true,
            'mappedBy'      => null,
            'inversedBy'    =>'a'
        )));
        $class1->expects($this->any())->method('isAssociationInverseSide')->will($this->returnValue(false));
        $class1->expects($this->any())->method('isCollectionValuedAssociation')->will($this->returnValue(true));
        $class1->expects($this->any())->method('getFieldNames')->will($this->returnValue(array()));

        $class2 = $this->getMockBuilder('Doctrine\\Common\\Persistence\\Mapping\\ClassMetadata')
            ->setMethods(array(
                'getName',
                'getIdentifier',
                'getReflectionClass',
                'isIdentifier',
                'hasField',
                'hasAssociation',
                'isSingleValuedAssociation',
                'isCollectionValuedAssociation',
                'getFieldNames',
                'getIdentifierFieldNames',
                'getAssociationNames',
                'getTypeOfField',
                'getAssociationTargetClass',
                'isAssociationInverseSide',
                'getAssociationMappedByTargetField',
                'getIdentifierValues',
                'getAssociationMapping',
            ))->getMock();

        $class2->expects($this->any())->method('getName')->will($this->returnValue('B'));
        $class2->expects($this->any())->method('getAssociationNames')->will($this->returnValue(array('a')));
        $class2->expects($this->any())->method('getAssociationTargetClass')->will($this->returnCallback(
            function ($target) {
                return strtoupper($target);
            }
        ));
        $class2->expects($this->any())->method('getAssociationMapping')->will($this->returnValue(array(
            'isOwningSide' => false,
            'mappedBy'      => 'b',
            'inversedBy'    => null
        )));
        $class2->expects($this->any())->method('isAssociationInverseSide')->will($this->returnValue(true));
        $class2->expects($this->any())->method('isCollectionValuedAssociation')->will($this->returnValue(true));
        $class2->expects($this->any())->method('getFieldNames')->will($this->returnValue(array()));

        $classStore = new classStore(array($class1, $class2));
        $stringGenerator = new StringGenerator($classStore);

        $this->assertSame('[A]<>a *-b *>[B]', $stringGenerator->getAssociationString($class1, 'b'));
        $this->assertSame('[B]<b *-a *<>[A]', $stringGenerator->getAssociationString($class2, 'a'));
    }
}
