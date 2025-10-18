<?php
// Endpoint to update order status
// POST: order_id, status
// Response: {success: bool, message: string, order_id, status}
header('Content-Type: application/json');
session_start();

if (!isset($_SESSION['OwnerID']) && !isset($_SESSION['EmployeeID'])) {
    http_response_code(401);
    echo json_encode(['success'=>false,'message'=>'Unauthorized']);
    exit;
}

$orderID = isset($_POST['order_id']) ? (int)$_POST['order_id'] : 0;
$status = isset($_POST['status']) ? trim($_POST['status']) : '';
// Optional rejection reason
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : null;

if ($orderID <= 0 || $status === '') {
    http_response_code(400);
    echo json_encode(['success'=>false,'message'=>'Missing parameters']);
    exit;
}

// Map front-end display labels to canonical DB values
$map = [
    'Preparing Order' => 'Preparing',
    'Order Ready' => 'Ready',
    'Pending' => 'Pending',
    'Complete' => 'Complete',
    'Rejected' => 'Rejected',
    'Reject' => 'Rejected'
];
if (isset($map[$status])) { $status = $map[$status]; }

require_once(__DIR__ . '/../classes/database.php');
$db = new database();
$result = $db->updateOrderStatus($orderID, $status, $reason);
if ($result['success']) {
        // Fire-and-forget: if status moved to Complete and this is a customer order, send email receipt.
        if ($status === 'Complete') {
                try {
                        // Fetch order + customer details
                        $receipt = $db->getOrderReceiptData($orderID);
                        if ($receipt && (int)$receipt['UserTypeID'] === 3 && filter_var($receipt['C_Email'] ?? '', FILTER_VALIDATE_EMAIL)) {
                                // Build and send email
                                require_once __DIR__ . '/../Mailer/class.phpmailer.php';
                                require_once __DIR__ . '/../Mailer/class.smtp.php';
                                $mailConfig = require __DIR__ . '/../classes/mail_config.php';

                                $mail = new PHPMailer;
                                $mail->CharSet    = 'UTF-8';
                                $mail->isSMTP();
                                $mail->Host       = $mailConfig['smtp']['host'];
                                $mail->Port       = (int)$mailConfig['smtp']['port'];
                                $mail->SMTPAuth   = true;
                                $mail->SMTPSecure = $mailConfig['smtp']['secure'];
                                $mail->SMTPDebug  = (int)$mailConfig['smtp']['debug'];
                                $mail->Debugoutput = function ($str, $level) { error_log("PHPMailer [$level]: $str"); };
                                $mail->Timeout    = (int)$mailConfig['smtp']['timeout'];
                                $mail->SMTPOptions = ['ssl' => $mailConfig['smtp']['ssl_options']];
                                $mail->Username   = $mailConfig['smtp']['username'];
                                $mail->Password   = $mailConfig['smtp']['password'];
                                $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);
                                $mail->addReplyTo($mailConfig['reply_to'], $mailConfig['from_name']);
                                $mail->addAddress($receipt['C_Email'], trim(($receipt['CustomerFN'] ?? '') . ' ' . ($receipt['CustomerLN'] ?? '')));
                                $mail->isHTML(true);

                                // Subject and body
                                $ref = $receipt['ReferenceNo'] ?? '';
                                $mail->Subject = 'Your receipt for Order #' . $orderID . ($ref ? " (Ref: $ref)" : '');

                                // Build a simple responsive HTML (inline styles)
                                $itemsRows = '';
                                if (!empty($receipt['Items'])) {
                                        foreach ($receipt['Items'] as $it) {
                                                $n = htmlspecialchars((string)$it['name']);
                                                $q = (int)$it['qty'];
                                                $u = number_format((float)$it['unit'], 2);
                                                $s = number_format((float)$it['subtotal'], 2);
                                                $itemsRows .= "<tr><td style=\"padding:6px 8px;border-bottom:1px solid #eee\">$n</td><td style=\"padding:6px 8px;border-bottom:1px solid #eee;text-align:center\">$q</td><td style=\"padding:6px 8px;border-bottom:1px solid #eee;text-align:right\">₱$u</td><td style=\"padding:6px 8px;border-bottom:1px solid #eee;text-align:right\">₱$s</td></tr>";
                                        }
                                }
                                $pickup = !empty($receipt['PickupAt']) ? date('M d, Y H:i', strtotime($receipt['PickupAt'])) : null;
                                $total  = number_format((float)$receipt['TotalAmount'], 2);
                                $pm     = htmlspecialchars((string)($receipt['PaymentMethod'] ?? ''));
                                $orderDt = date('M d, Y H:i', strtotime($receipt['OrderDate']));

                                $mail->Body = "
                                <div style='font-family:Inter,Segoe UI,Tahoma,sans-serif;background:#F7F2EC;padding:24px;'>
                                    <div style='max-width:660px;margin:0 auto;background:#fff;border:1px solid #eadfd2;border-radius:12px;overflow:hidden;'>
                                        <div style='background:#7C573A;color:#fff;padding:16px 20px;'>
                                            <h2 style='margin:0;font-size:18px;font-weight:700'>Love Amaiah Cafe — Receipt</h2>
                                        </div>
                                        <div style='padding:18px 20px;color:#21160E;'>
                                            <p style='margin:0 0 8px;'>Hi " . htmlspecialchars((string)($receipt['CustomerFN'] ?? $receipt['C_Username'] ?? 'there')) . ",</p>
                                            <p style='margin:0 0 12px;'>Thanks for your order. Here are your receipt details.</p>
                                            <div style='margin:10px 0;padding:10px 12px;border:1px dashed #C4A07A;border-radius:8px;background:#fffaf5;'>
                                                <div><strong>Order #:</strong> $orderID</div>
                                                " . ($ref ? "<div><strong>Reference #:</strong> " . htmlspecialchars($ref) . "</div>" : '') . "
                                                <div><strong>Order date:</strong> $orderDt</div>
                                                " . ($pickup ? "<div><strong>Pickup time:</strong> $pickup</div>" : '') . "
                                                <div><strong>Payment:</strong> $pm</div>
                                            </div>
                                            <table role='presentation' cellpadding='0' cellspacing='0' width='100%' style='border-collapse:collapse;margin-top:8px'>
                                                <thead>
                                                    <tr>
                                                        <th align='left'  style='padding:6px 8px;border-bottom:2px solid #c19a6b;color:#4B2E0E;font-size:12px'>Item</th>
                                                        <th align='center'style='padding:6px 8px;border-bottom:2px solid #c19a6b;color:#4B2E0E;font-size:12px'>Qty</th>
                                                        <th align='right' style='padding:6px 8px;border-bottom:2px solid #c19a6b;color:#4B2E0E;font-size:12px'>Unit</th>
                                                        <th align='right' style='padding:6px 8px;border-bottom:2px solid #c19a6b;color:#4B2E0E;font-size:12px'>Subtotal</th>
                                                    </tr>
                                                </thead>
                                                <tbody>$itemsRows</tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan='3' style='padding:10px 8px;text-align:right;font-weight:700;border-top:2px solid #eee'>Total</td>
                                                        <td style='padding:10px 8px;text-align:right;font-weight:700;border-top:2px solid #eee'>₱$total</td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                            <p style='margin:16px 0 0;font-size:12px;color:#6b5a4b'>If you have any questions, just reply to this email.</p>
                                        </div>
                                    </div>
                                </div>";
                                $mail->AltBody = "Love Amaiah Cafe — Receipt\nOrder #$orderID" . ($ref?"\nReference: $ref":"") . "\nDate: $orderDt" . ($pickup?"\nPickup: $pickup":"") . "\nPayment: $pm\nTotal: ₱$total";

                                // Attach a downloadable copy of the HTML receipt (customers can save/print)
                                try {
                                    $mail->addStringAttachment($mail->Body, "receipt_{$orderID}.html", 'base64', 'text/html');
                                } catch (Exception $e) {
                                    // attachment failed - continue without blocking
                                    error_log('Attach receipt error: ' . $e->getMessage());
                                }

                                // Try to send, but don't block success if it fails
                                $mail->send();
                        }
                } catch (Throwable $e) {
                        error_log('Email receipt error: ' . $e->getMessage());
                }
        }
        echo json_encode(['success'=>true,'message'=>'Status updated','order_id'=>$orderID,'status'=>$status]);
} else {
        http_response_code(500);
        echo json_encode(['success'=>false,'message'=>$result['message'],'order_id'=>$orderID]);
}
