<?php
/** no direct access **/
defined('MECEXEC') or die();

/**
 * Webnus MEC Base class.
 * @author Webnus <info@webnus.net>
 * @abstract
 */
abstract class MEC_base extends MEC
{
    /**
     * Returns MEC_db instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_db instance
     */
	final public function getDB()
    {
        return MEC::getInstance('app.libraries.db');
    }
    
    /**
     * Returns MEC_file instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_file instance
     */
    final public function getFile()
    {
        return MEC::getInstance('app.libraries.filesystem', 'MEC_file');
    }
    
    /**
     * Returns MEC_folder instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_folder instance
     */
    final public function getFolder()
    {
        return MEC::getInstance('app.libraries.filesystem', 'MEC_folder');
    }
    
    /**
     * Returns MEC_path instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_path instance
     */
    final public function getPath()
    {
        return MEC::getInstance('app.libraries.filesystem', 'MEC_path');
    }
    
    /**
     * Returns MEC_main instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_main instance
     */
    final public function getMain()
    {
        return MEC::getInstance('app.libraries.main');
    }
    
    /**
     * Returns MEC_factory instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_factory instance
     */
    final public function getFactory()
    {
        return MEC::getInstance('app.libraries.factory');
    }
    
    /**
     * Returns MEC_render instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_render instance
     */
    final public function getRender()
    {
        return MEC::getInstance('app.libraries.render');
    }
    
    /**
     * Returns MEC_parser instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_parser instance
     */
    final public function getParser()
    {
        return MEC::getInstance('app.libraries.parser');
    }
    
    /**
     * Returns MEC_feed instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_feed instance
     */
    final public function getFeed()
    {
        return MEC::getInstance('app.libraries.feed');
    }
    
    /**
     * Returns MEC_book instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_book instance
     */
    final public function getBook()
    {
        return MEC::getInstance('app.libraries.book');
    }

    /**
     * Returns MEC_capacity instance
     * @final
     * @return MEC_capacity
     */
    final public function getCapacity()
    {
        return MEC::getInstance('app.libraries.capacity');
    }
    
    /**
     * Returns MEC_notifications instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_notifications instance
     */
    final public function getNotifications()
    {
        return MEC::getInstance('app.libraries.notifications');
    }

    /**
     * Returns QRCode instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return QRcode instance
     */
    final public function getQRcode()
    {
        self::import('app.libraries.qrcode');
        return new QRcode();
    }

    /**
     * Is this the Pro *package*?
     *
     * Answers "which files shipped", never "is this site licensed". Anything a
     * site needs in order to RECOVER — the activation form, its AJAX handlers,
     * the update checker — must key on this rather than on getPRO(), otherwise
     * an unlicensed Pro install has no route back and is unrecoverable without
     * FTP.
     *
     * Lite does not ship the licence core, so this is false there. It must
     * never fatal when the class is absent, because this file is byte-identical
     * between the two packages.
     *
     * @final
     * @author Webnus <info@webnus.net>
     * @return bool
     */
    final public function isProBuild()
    {
        return class_exists('MEC_license');
    }

    /**
     * May Pro features run on this site?
     *
     * Boolean by contract. Every one of the ~130 call sites treats the result
     * as truthy/falsy, so this must NOT be widened to return a phase. Code that
     * needs the intermediate stages asks MEC_license::instance()->phase().
     *
     * False here means exact Lite parity, which is the final phase of the ramp
     * and also what Lite itself returns.
     *
     * @final
     * @author Webnus <info@webnus.net>
     * @return bool
     */
    final public function getPRO()
    {
        if (!class_exists('MEC_license')) return false;

        return MEC_license::instance()->phase() < MEC_license::PHASE_LITE;
    }

    /**
     * Current enforcement phase, 0-4. Always 0 on a licensed site, and always
     * PHASE_LITE where the licence core is absent (Lite).
     *
     * @final
     * @author Webnus <info@webnus.net>
     * @return int
     */
    final public function getLicensePhase()
    {
        if (!class_exists('MEC_license')) return 4;

        return MEC_license::instance()->phase();
    }

    /**
     * May NEW Pro configuration be created? (phase 1)
     *
     * False means: no new coupons, no new ticket variations, no enabling a Pro
     * feature on an event that does not already have it. Everything already
     * configured keeps working, keeps rendering and keeps being editable — this
     * gate is about adding, never about what exists.
     *
     * Read it at the point of WRITING, not the point of reading, so that
     * existing content is never re-evaluated against it.
     *
     * @final
     * @author Webnus <info@webnus.net>
     * @return bool
     */
    final public function isProCreationEnabled()
    {
        // Note: && not `and`. `return $a and $b;` returns $a.
        return $this->getPRO() && $this->getLicensePhase() < 1;
    }

    /**
     * May the Pro presentation layer run? (phase 2)
     *
     * Covers Pro-only skins, advanced search, automated/scheduled notifications
     * and Pro addons. Everything here is display or convenience: switching it
     * off changes what a visitor sees, never what is stored, and never what is
     * charged. Booking is deliberately NOT in this tier — see
     * isBookingUnitEnabled().
     *
     * @final
     * @author Webnus <info@webnus.net>
     * @return bool
     */
    final public function isProPresentationEnabled()
    {
        // Note: && not `and`. `return $a and $b;` returns $a.
        return $this->getPRO() && $this->getLicensePhase() < 2;
    }

    /**
     * May the booking unit run?
     *
     * Booking, payment gateways, coupons and organizer payments switch off
     * TOGETHER or not at all. They must never be gated independently:
     *
     *  - dropping coupons alone silently changes what customers are charged;
     *  - dropping op.php alone lets MEC_feature_op::op() stop overwriting the
     *    gateway credentials, so organizers' money is paid to the site owner
     *    instead. That is misdirected money, not a disabled feature.
     *
     * Existing bookings, transactions and attendee lists stay readable and
     * exportable regardless — this gate only stops NEW bookings.
     *
     * @final
     * @author Webnus <info@webnus.net>
     * @return bool
     */
    final public function isBookingUnitEnabled()
    {
        // Note: && not `and`. `return $a and $b;` returns $a.
        return $this->getPRO() && $this->getLicensePhase() < 3;
    }

    /**
     * Returns PRO instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_schedule instance
     */
    final public function getSchedule()
    {
        return MEC::getInstance('app.libraries.schedule');
    }

    /**
     * Returns PRO instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_syncSchedule instance
     */
    final public function getSyncSchedule()
    {
        return MEC::getInstance('app.libraries.syncSchedule');
    }

    /**
     * Returns Cache instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_cache instance
     */
    final public function getCache()
    {
        MEC::import('app.libraries.cache');
        return MEC_cache::getInstance();
    }

    /**
     * Returns WC instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_wc instance
     */
    final public function getWC()
    {
        return MEC::getInstance('app.libraries.wc');
    }

    /**
     * Returns User instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_user instance
     */
    final public function getUser()
    {
        return MEC::getInstance('app.libraries.user');
    }

    /**
     * Returns Form Builder instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_formBuilder instance
     */
    final public function getFormBuilder()
    {
        return MEC::getInstance('app.libraries.formBuilder');
    }

    /**
     * Returns Event Fields instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_eventFields instance
     */
    final public function getEventFields()
    {
        return MEC::getInstance('app.libraries.eventFields');
    }

    /**
     * Returns Search instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_search instance
     */
    final public function getSearch()
    {
        return MEC::getInstance('app.libraries.search');
    }

    /**
     * Returns Ticket Variations instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_ticketVariations instance
     */
    final public function getTicketVariations()
    {
        return MEC::getInstance('app.libraries.ticketVariations');
    }

    /**
     * Returns Booking Record instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_bookingRecord instance
     */
    final public function getBookingRecord()
    {
        return MEC::getInstance('app.libraries.bookingRecord');
    }

    /**
     * Returns MEC Cart instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_cart instance
     */
    final public function getCart()
    {
        return MEC::getInstance('app.libraries.cart');
    }

    /**
     * Returns Partial Payment instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_partial instance
     */
    final public function getPartialPayment()
    {
        return MEC::getInstance('app.libraries.partial');
    }

    /**
     * Returns Captcha instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_captcha instance
     */
    final public function getCaptcha()
    {
        return MEC::getInstance('app.libraries.captcha');
    }

    /**
     * Returns Tickets instance
     * @final
     * @author Webnus <info@webnus.net>
     * @return MEC_tickets instance
     */
    final public function getTickets()
    {
        return MEC::getInstance('app.libraries.tickets');
    }

    /**
     * @return MEC_meetup
     */
    final public function getMeetup()
    {
        return MEC::getInstance('app.libraries.meetup');
    }

    /**
     * @return MEC_restful
     */
    final public function getRestful()
    {
        return MEC::getInstance('app.libraries.restful');
    }

    /**
     * @return MEC_appointments
     */
    final public function getAppointments()
    {
        return MEC::getInstance('app.libraries.appointments');
    }
}
