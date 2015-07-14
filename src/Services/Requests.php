<?php
/**
 * Requests.php
 *
 * Part of Allinpay.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @author    Fackeronline <1077341744@qq.com>
 * @link      https://github.com/Fakeronline
 */

namespace Fakeronline\Chinapnr\Services;
use Exception;
use Fakeronline\Chinapnr\Utils\Arr;
use Fakeronline\Chinapnr\Tools\Encrypt;

abstract class Requests{

    const VERSION_10 = '10';

    /**
     * �������
     * @var array
     */
    protected $config = [];

    /**
     * ��������
     * @var array
     */
    protected $attribute = [];

    /**
     * �������ֵ
     * @var array
     */
    protected $value = [];

    /**
     * �����ֶ����õ�����
     * @var array
     */

    /**
     * ����˳������
     * @var array
     */
    protected $sortAttribute = [];

    protected $requiredAttr = [];

    /**
     * �ܱ����Ĳ���KEY����������ָ
     * @var array
     */
    protected $guarded = ['CmdId']; //Ŀǰֻ����Ϣ���Ͳ����ֶ�����


    public function __construct($url, array $key, $merCustId){

        if(empty($url) || empty($key) || empty($merCustId)){

            throw new Exception('URL��KEY��MERCUST_IDΪ��Ҫ����������KEY����Ϊ���飬����˽��KEY�͹���KEY!');

        }

        $this->config = [
            'url' => $url,
            'privateKey' => Arr::get($key, 'privateKey', ''),
            'publicKey' => Arr::get($key, 'publicKey', ''),
            'merCustId' => $merCustId
        ];

        $this->attribute = (array)($this->attribute()); //������Բ���
        $this->sortAttribute = (array)($this->sortAttribute()); //��ȡ����˳�����
        $this->requiredAttr = (array)($this->requiredAttr());   //��ȡ��Ҫ����

        $this->value['Version'] = self::VERSION_10; //���ð汾�ţ�Ĭ��Ϊ10�汾

        $className = explode('\\', get_class($this));   //��̬��ȡ����
        $this->value['CmdId'] = end($className);    //���ò���

        $this->value['MerCustId'] = $this->config['merCustId']; //�����̻���

    }

    /**
     * ���ð汾��
     * @param string $version   �汾��
     * @return $this    ��ǰ����
     * @throws Exception
     */
    public function setVersion($version = '10'){

        if($version != self::VERSION_10){
            throw new Exception('�ݲ�֧�ִ˰汾!');
        }

        $this->value['Version'] = $version;

        return $this;

    }


    /**
     * ���ýӿ�Ӧ���ַ
     * @param string $bgUrl    ��̨Ӧ���ַ
     * @param string $recUrl    ǰ̨Ӧ���ַ
     * @return $this    ��ǰ����
     * @throws Exception
     */
    public function setUrl($bgUrl, $recUrl = ''){

        if(empty($bgUrl)){
            throw new Exception('�̻���̨Ӧ���ַ����Ϊ��!');
        }

        $this->value['BgRetUrl'] = $bgUrl;

        $this->value['RetUrl'] = $recUrl;

        return $this;

    }

    /**
     * ǩ��
     * @return string
     */
    final protected function sign(){

        $result = $this->sortArgs($this->sortAttribute, $this->value);
        $resultStr = implode('', $result);

        $encrypt = new Encrypt();
        $resultStr = $encrypt->secureToolSha1_128($resultStr);
        return $encrypt->secureToolRsaEncrypt($resultStr, $encrypt->buildKeyStr($this->config['privateKey']));
    }

    /**
     * �����������
     * @return array ��������
     */
    abstract protected function attribute();

    abstract protected function sortAttribute();

    abstract protected function requiredAttr();

    abstract public function params($args);

    abstract public function request();

    public function __get($key){

        return Arr::get($this->value, $key);

    }

    public function __set($key, $value){

        if(in_array($key, $this->attribute) && (!in_array($key, $this->guarded))){
            $this->value[$key] = $value;
        }

    }

    public function __call($method, $args){

        $this->__set($method, $args);

    }

}