<?php

namespace App\Enums;

enum SellerInteractionEventType: string
{
    case ProfileView = 'profile_view';
    case WhatsappClick = 'whatsapp_click';
}
