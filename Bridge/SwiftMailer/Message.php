<?php
/**
 * Webiny Framework (http://www.webiny.com/framework)
 *
 * @copyright Copyright Webiny LTD
 */

namespace Webiny\Component\Mailer\Bridge\SwiftMailer;

use Webiny\Component\Config\ConfigObject;
use Webiny\Component\Mailer\Bridge\MessageInterface;
use Webiny\Component\Mailer\MailerException;
use Webiny\Component\StdLib\StdLibTrait;
use Webiny\Component\StdLib\StdObject\ArrayObject\ArrayObject;
use Webiny\Component\Storage\File\LocalFile;
use Webiny\Component\Mailer\Email;

/**
 * Bridge to SwiftMailer Message class.
 *
 * @package         Webiny\Component\Mailer\Bridge\SwiftMailer
 */
class Message implements MessageInterface
{
    use StdLibTrait;

    /**
     * @var \Swift_Message
     */
    private $_message;

    public function __construct(ConfigObject $config = null)
    {
        $this->_message = new \Swift_Message();

        if ($config) {
            $this->_message->setCharset($config->get('CharacterSet', 'utf-8'));
            $this->_message->setMaxLineLength($config->get('MaxLineLength', 78));

            if ($config->get('Priority', false)) {
                $this->_message->setPriority($config->get('Priority', 3));
            }
        }
    }

    public function __invoke()
    {
        return $this->_message;
    }

    /**
     * Set the message subject.
     *
     * @param string $subject Message subject.
     *
     * @return $this
     */
    public function setSubject($subject)
    {
        $this->_message->setSubject($subject);

        return $this;
    }

    /**
     * Get the current message subject.
     *
     * @return string Message subject.
     */
    public function getSubject()
    {
        return $this->_message->getSubject();
    }

    /**
     * Returns the person who sent the message.
     *
     * @return Email
     */
    public function getFrom()
    {
        return $this->_message->getFrom();
    }

    /**
     * Return the person who sent the message.
     *
     * @return Email
     */
    public function getSender()
    {
        return new Email($this->_message->getSender());
    }

    /**
     * Returns a list of defined recipients.
     *
     * @return array
     */
    public function getTo()
    {
        $recipients = [];
        foreach ($this->_message->getTo() as $email => $name) {
            $recipients[] = new Email($email, $name);
        }

        return $recipients;
    }

    /**
     * Returns a list of addresses to whom the message will be copied to.
     *
     * @return array
     */
    public function getCc()
    {
        $recipients = [];
        foreach ($this->_message->getCc() as $email => $name) {
            $recipients[] = new Email($email, $name);
        }

        return $recipients;
    }

    /**
     * Returns a list of defined bcc recipients.
     *
     * @return array
     */
    public function getBcc()
    {
        $recipients = [];
        foreach ($this->_message->getBcc() as $email => $name) {
            $recipients[] = new Email($email, $name);
        }

        return $recipients;
    }

    /**
     * Returns the reply-to address.
     *
     * @return Email
     */
    public function getReplyTo()
    {
        $replyTo = $this->_message->getReplyTo();

        return new Email($replyTo);
    }

    /**
     * Set the message body.
     *
     * @param string $content The content of the body.
     * @param string $type    Content type. Default 'text/html'.
     * @param string $charset Content body charset. Default 'utf-8'.
     *
     * @return \Webiny\Component\Mailer\MessageInterface
     */
    public function setBody($content, $type = 'text/html', $charset = 'utf-8')
    {
        $this->_message->setBody($content, $type, $charset);

        return $this;
    }

    /**
     * Returns the body of the message.
     *
     * @return string
     */
    public function getBody()
    {
        return $this->_message->getBody();
    }

    /**
     * Defines the return path for the email.
     * By default it should be set to the sender.
     *
     * @param string $returnPath
     *
     * @return $this
     */
    public function setReturnPath($returnPath)
    {
        $this->_message->setReturnPath($returnPath);

        return $this;
    }

    /**
     * Returns the defined return-path.
     *
     * @return string
     */
    public function getReturnPath()
    {
        return $this->_message->getReturnPath();
    }

    /**
     * Specifies the format of the message (usually text/plain or text/html).
     *
     * @param string $contentType
     *
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->_message->setContentType($contentType);

        return $this;
    }

    /**
     * Returns the defined content type of the message.
     *
     * @return string
     */
    public function getContentType()
    {
        return $this->_message->getContentType();
    }

    /**
     * Attach a file to your message.
     *
     * @param LocalFile $file     File instance
     * @param string    $fileName Optional name that will be set for the attachment.
     * @param string    $type     Optional MIME type of the attachment
     *
     * @return $this
     */
    public function addAttachment(LocalFile $file, $fileName = '', $type = 'plain/text')
    {
        $attachment = new \Swift_Attachment($file->getContents(), $fileName, $type);
        $this->_message->attach($attachment);

        return $this;
    }

    /**
     * Specifies the encoding scheme in the message.
     *
     * @param string $encoding
     *
     * @return $this
     * @throws SwiftMailerException
     */
    public function setContentTransferEncoding($encoding)
    {
        switch ($encoding) {
            case '7bit':
                $encoder = \Swift_Encoding::get7BitEncoding();
                break;

            case '8bit':
                $encoder = \Swift_Encoding::get8BitEncoding();
                break;

            case 'base64':
                $encoder = \Swift_Encoding::getBase64Encoding();
                break;

            case 'qp':
                $encoder = \Swift_Encoding::getQpEncoding();
                break;

            default:
                throw new SwiftMailerException('Invalid encoding name provided.
												Valid encodings are [7bit, 8bit, base64, qp].'
                );
                break;
        }

        $this->_message->setEncoder($encoder);

        return $this;
    }

    /**
     * Get the defined encoding scheme.
     *
     * @return string
     */
    public function getContentTransferEncoding()
    {
        return $this->_message->getEncoder()->getName();
    }

    public function setSender(Email $sender)
    {
        $this->_message->setSender($sender->email, $sender->name);

        return $this;
    }

    public function setFrom(Email $from)
    {
        $this->_message->setFrom($from->email, $from->name);

        return $this;
    }

    public function setReplyTo(Email $replyTo)
    {
        $this->_message->setReplyTo($replyTo->email, $replyTo->name);

        return $this;
    }

    public function setTo($to)
    {
        if ($to instanceof Email) {
            $to = [$to];
        }

        foreach ($to as $email) {
            if (!$email instanceof Email) {
                throw new MailerException('Email must be an instance of \Webiny\Component\Mailer\Email.');
            }
            $this->addTo($email);
        }

        return $this;
    }

    public function setCc($cc)
    {
        if ($cc instanceof Email) {
            $cc = [$cc];
        }

        foreach ($cc as $email) {
            if (!$email instanceof Email) {
                throw new MailerException('Email must be an instance of \Webiny\Component\Mailer\Email.');
            }
            $this->addCc($email);
        }

        return $this;
    }

    public function setBcc($bcc)
    {
        if ($bcc instanceof Email) {
            $bcc = [$bcc];
        }

        foreach ($bcc as $email) {
            if (!$email instanceof Email) {
                throw new MailerException('Email must be an instance of \Webiny\Component\Mailer\Email.');
            }
            $this->addBcc($email);
        }

        return $this;
    }

    public function addTo(Email $email)
    {
        $this->_message->addTo($email->email, $email->name);

        return $this;
    }

    public function addFrom(Email $email)
    {
        $this->_message->addFrom($email->email, $email->name);

        return $this;
    }

    public function addCc(Email $email)
    {
        $this->_message->addCc($email->email, $email->name);

        return $this;
    }

    public function addBcc(Email $email)
    {
        $this->_message->addBcc($email->email, $email->name);

        return $this;
    }

    /**
     * Set multiple headers
     *
     * @param array|ArrayObject $headers
     *
     * @return $this
     */
    public function setHeaders($headers)
    {
        foreach ($headers as $key => $value) {
            $this->addHeader($key, $value);
        }

        return $this;
    }

    /**
     * Adds a header to the message.
     *
     * @param string     $name   Header name.
     * @param string     $value  Header value.
     * @param null|array $params Optional array of parameters.
     *
     * @return $this
     */
    public function addHeader($name, $value, $params = null)
    {
        if (is_array($params)) {
            $this->_message->getHeaders()->addParameterizedHeader($name, $value, $params);
        } else {
            $this->_message->getHeaders()->addTextHeader($name, $value);
        }

        return $this;
    }

    /**
     * Get a header from the message.
     *
     * @param string $name Header name.
     *
     * @return mixed
     */
    public function getHeader($name)
    {
        return $this->_message->getHeaders()->get($name)->getFieldBody();
    }

    /**
     * Get all headers from the message.
     *
     * @return array
     */
    public function getHeaders()
    {
        $swiftHeaders = $this->_message->getHeaders()->listAll();
        $headers = [];
        foreach ($swiftHeaders as $headerName) {
            $headers[$headerName] = $this->getHeader($headerName);
        }

        return $headers;
    }

    public function getChildren()
    {
        return $this->_message->getChildren();
    }
}