<?php

namespace Persona\Hris\Core\Security\Subscriber;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Event\JWTExpiredEvent;
use Lexik\Bundle\JWTAuthenticationBundle\Events;
use Persona\Hris\Core\Manager\ManagerFactory;
use Persona\Hris\Core\Security\Model\UserInterface;
use Persona\Hris\Core\Security\Model\UserRepositoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

/**
 * @author Muhamad Surya Iksanudin <surya.iksanudin@personahris.com>
 */
final class UserSessionSubscriber implements EventSubscriberInterface
{
    /**
     * @var SessionInterface
     */
    private $session;

    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var ManagerFactory
     */
    private $managerFactory;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @param KernelInterface         $kernel
     * @param ManagerFactory          $managerFactory
     * @param UserRepositoryInterface $userRepository
     * @param SessionInterface        $session
     */
    public function __construct(
        KernelInterface $kernel,
        ManagerFactory $managerFactory,
        UserRepositoryInterface $userRepository,
        SessionInterface $session
    ) {
        $this->kernel = $kernel;
        $this->managerFactory = $managerFactory;
        $this->userRepository = $userRepository;
        $this->session = $session;
    }

    /**
     * @param JWTExpiredEvent $event
     */
    public function resetSession(JWTExpiredEvent $event)
    {
        $user = $this->userRepository->findUserBySessionId((string) $this->session->get(UserInterface::SESSION_KEY));
        if (!$user instanceof UserInterface) {
            return;
        }

        $user->setSessionId('');
        $user->setLoggedIn(false);

        $this->session->remove(UserInterface::SESSION_KEY);

        $persister = $this->managerFactory->getWriteManager();
        $persister->persist($user);
        $persister->flush();
    }

    /**
     * @param AuthenticationSuccessEvent $event
     */
    public function startSession(AuthenticationSuccessEvent $event)
    {
        $user = $event->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        $this->session->set(UserInterface::SESSION_KEY, sha1(date('YmdHis')));

        $user->setLoggedIn(true);
        $user->setSessionId($this->session->get(UserInterface::SESSION_KEY));

        $persister = $this->managerFactory->getWriteManager();
        $persister->persist($user);
        $persister->flush();
    }

    /**
     * @param InteractiveLoginEvent $event
     */
    public function checkSession(InteractiveLoginEvent $event)
    {
        if ('prod' !== $this->kernel->getEnvironment()) {
            return;
        }

        $user = $event->getAuthenticationToken()->getUser();
        if (!$user instanceof UserInterface) {
            return;
        }

        $userPersist = $this->userRepository->find($user->getId());
        if ($userPersist->isLoggedIn($user, $this->session)) {
            throw new AuthenticationException();
        }
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            Events::AUTHENTICATION_SUCCESS => ['startSession', 1],
            Events::JWT_EXPIRED => ['resetSession', 1],
            SecurityEvents::INTERACTIVE_LOGIN => ['checkSession', 1],
        ];
    }
}
