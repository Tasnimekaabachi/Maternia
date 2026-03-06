<?php

namespace App\Controller\Api;

use App\Entity\Conversation;
use App\Entity\Message;
use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use App\Repository\OffreBabySitterRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/messagerie', name: 'api_messagerie_')]
final class MessagerieApiController extends AbstractController
{
    #[Route('/conversations', name: 'conversations', methods: ['GET'])]
    public function listConversations(Request $request, ConversationRepository $conversationRepo, OffreBabySitterRepository $offreRepo): JsonResponse
    {
        $email = $request->query->get('email');
        $offreId = $request->query->get('offre_id');
        if ($email !== null && $email !== '') {
            $conversations = $conversationRepo->findByParentEmail($email);
        } elseif ($offreId !== null && $offreId !== '') {
            $offre = $offreRepo->find((int) $offreId);
            if (!$offre) {
                return $this->json(['error' => 'Offre introuvable.'], 404);
            }
            $conversations = $conversationRepo->findByOffre($offre);
        } else {
            return $this->json(['error' => 'Préciser email (parent) ou offre_id (babysitter).'], 400);
        }
        $data = array_map(static function (Conversation $c) {
            $dernier = $c->getDernierMessage();
            return [
                'id' => $c->getId(),
                'parent_email' => $c->getParentEmail(),
                'parent_name' => $c->getParentName(),
                'offre_id' => $c->getOffre()?->getId(),
                'offre_nom' => $c->getOffre()?->getNomBabysitter(),
                'created_at' => $c->getCreatedAt()?->format(\DateTimeInterface::ATOM),
                'last_message' => $dernier ? [
                    'contenu' => $dernier->getContenu(),
                    'envoye_par' => $dernier->getEnvoyePar(),
                    'created_at' => $dernier->getCreatedAt()?->format(\DateTimeInterface::ATOM),
                ] : null,
            ];
        }, $conversations);
        return $this->json($data);
    }

    #[Route('/conversations', name: 'conversations_create', methods: ['POST'])]
    public function createConversation(Request $request, EntityManagerInterface $em, OffreBabySitterRepository $offreRepo, ConversationRepository $conversationRepo): JsonResponse
    {
        $body = json_decode($request->getContent(), true) ?? [];
        $offreId = (int) ($body['offre_id'] ?? 0);
        $parentEmail = (string) ($body['parent_email'] ?? '');
        $parentName = (string) ($body['parent_name'] ?? '');
        if ($offreId <= 0 || $parentEmail === '' || $parentName === '') {
            return $this->json(['error' => 'offre_id, parent_email et parent_name requis.'], 400);
        }
        $offre = $offreRepo->find($offreId);
        if (!$offre) {
            return $this->json(['error' => 'Offre introuvable.'], 404);
        }
        $existing = $conversationRepo->findOneByOffreAndParent($offre, $parentEmail);
        if ($existing) {
            return $this->json([
                'id' => $existing->getId(),
                'parent_email' => $existing->getParentEmail(),
                'parent_name' => $existing->getParentName(),
                'offre_id' => $existing->getOffre()?->getId(),
                'created_at' => $existing->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            ], 201);
        }
        $conversation = new Conversation();
        $conversation->setOffre($offre);
        $conversation->setParentEmail($parentEmail);
        $conversation->setParentName($parentName);
        $em->persist($conversation);
        $em->flush();
        return $this->json([
            'id' => $conversation->getId(),
            'parent_email' => $conversation->getParentEmail(),
            'parent_name' => $conversation->getParentName(),
            'offre_id' => $conversation->getOffre()?->getId(),
            'created_at' => $conversation->getCreatedAt()?->format(\DateTimeInterface::ATOM),
        ], 201);
    }

    #[Route('/conversations/{id}/messages', name: 'messages_list', methods: ['GET'])]
    public function listMessages(int $id, ConversationRepository $conversationRepo, MessageRepository $messageRepo): JsonResponse
    {
        $conversation = $conversationRepo->find($id);
        if (!$conversation) {
            return $this->json(['error' => 'Conversation introuvable.'], 404);
        }
        $messages = $messageRepo->findByConversationOrdered($id);
        $data = array_map(static function (Message $m) {
            return [
                'id' => $m->getId(),
                'contenu' => $m->getContenu(),
                'envoye_par' => $m->getEnvoyePar(),
                'created_at' => $m->getCreatedAt()?->format(\DateTimeInterface::ATOM),
            ];
        }, $messages);
        return $this->json($data);
    }

    #[Route('/conversations/{id}/messages', name: 'messages_send', methods: ['POST'])]
    public function sendMessage(int $id, Request $request, ConversationRepository $conversationRepo, EntityManagerInterface $em): JsonResponse
    {
        $conversation = $conversationRepo->find($id);
        if (!$conversation) {
            return $this->json(['error' => 'Conversation introuvable.'], 404);
        }
        $body = json_decode($request->getContent(), true) ?? [];
        $contenu = (string) ($body['contenu'] ?? '');
        $envoyePar = (string) ($body['envoye_par'] ?? '');
        if ($contenu === '' || !\in_array($envoyePar, [Message::ENVOYE_PAR_PARENT, Message::ENVOYE_PAR_BABYSITTER], true)) {
            return $this->json(['error' => 'contenu et envoye_par (parent|babysitter) requis.'], 400);
        }
        $message = new Message();
        $message->setConversation($conversation);
        $message->setContenu($contenu);
        $message->setEnvoyePar($envoyePar);
        $em->persist($message);
        $em->flush();
        return $this->json([
            'id' => $message->getId(),
            'contenu' => $message->getContenu(),
            'envoye_par' => $message->getEnvoyePar(),
            'created_at' => $message->getCreatedAt()?->format(\DateTimeInterface::ATOM),
        ], 201);
    }
}
