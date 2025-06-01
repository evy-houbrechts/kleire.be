<?php
/*
Template Name: Bestellingen Overzicht (Winkelmanager)
*/

if (!is_user_logged_in() || !current_user_can('manage_woocommerce')) {
    wp_redirect(home_url());
    exit;
}

// Bestelstatus bijwerken
if (isset($_POST['order_id'], $_POST['order_status'])) {
    $order_id = intval($_POST['order_id']);
    $new_status = sanitize_text_field($_POST['order_status']);

    $order = wc_get_order($order_id);
    if ($order) {
        $order->update_status($new_status);
        echo '<p class="bg-accent text-text p-3 rounded">Bestelstatus succesvol bijgewerkt!</p>';
    }
}

// Filters
$status_filter = isset($_GET['status']) ? sanitize_text_field($_GET['status']) : '';
$search_order = isset($_GET['search_order']) ? sanitize_text_field($_GET['search_order']) : '';

$args = [
    'limit' => -1,
    'status' => $status_filter ? [$status_filter] : array_keys(wc_get_order_statuses()),
    'search' => $search_order,
];

$orders = wc_get_orders($args);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bestellingen</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f8f8f8;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 900px;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            margin: auto;
        }
        h2 {
            text-align: center;
            color: #4a4441;
        }
        label {
            font-size: 16px;
            font-weight: bold;
            color: #4a4441;
            display: block;
            margin-top: 12px;
        }
        .input-field, select {
            width: 100%;
            padding: 8px;
            border: 1px solid #c7c789;
            border-radius: 5px;
            font-size: 14px;
            margin-top: 5px;
        }
        .btn {
            display: inline-block;
            background-color: #b48c88;
            color: white;
            padding: 10px 15px;
            border-radius: 5px;
            text-align: center;
            font-size: 14px;
            border: none;
            cursor: pointer;
            margin-top: 10px;
        }
        .btn:hover {
            background-color: #a37772;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #c7c789;
            padding: 8px;
            text-align: left;
            font-size: 14px;
        }
        th {
            background-color: #c7c789;
            color: #4a4441;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .order-actions {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Overzicht van Bestellingen</h2>
        <a href="<?php echo home_url(); ?>" style="float: right;">Terug naar Home</a>
        <a href="/claire" style="float: left;">Terug naar Claire</a>
        <br>
        <form method="get">
            <label for="status">Filter op status:</label>
            <select name="status" class="input-field">
                <option value="">Alle statussen</option>
                <?php foreach (wc_get_order_statuses() as $status => $label) : ?>
                    <option value="<?php echo esc_attr($status); ?>" <?php selected($status_filter, $status); ?>>
                        <?php echo esc_html($label); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            
            <label for="search_order">Zoek op bestelnummer:</label>
            <input type="text" name="search_order" placeholder="Bestelnummer" value="<?php echo esc_attr($search_order); ?>" class="input-field">
            
            <button type="submit" class="btn">Filter toepassen</button>
        </form>

        <details style="width: 100%; padding: 8px;"> 
        <summary>Hier meer uitleg over Acties</summary>
        <p>Als je van actie verandert, krijgt de klant automatisch een mail.</p>
        <ul>
            <li><strong>Wachtend op betaling</strong> = Wordt automatisch ingesteld als de klant heeft besteld.<br>
                - Mail: “Bedankt voor je bestelling, wij wachten op zichtbaarheid betaling.”</li>

            <li><strong>In behandeling</strong> = Deze dien je zelf aan te duiden wanneer de klant heeft betaald en je over kunt gaan met inpakken en naar de post rijden.<br>
                - Mail: “Je betaling is goed verlopen, we behandelen nu je bestelling.”</li>

            <li><strong>Afgerond</strong> = Wanneer je de bestelling op de post hebt gedaan.<br>
                - Mail: “Je bestelling is verzonden.”</li>

            <li><strong>Geannuleerd</strong> = Dit kan je gebruiken als de klant annuleert, alvorens de bestelling is verzonden.<br>
                - Mail: “Je bestelling is geannuleerd.”</li>

            <li><strong>Terugbetaald</strong> = Dit kan je gebruiken wanneer de bestelling werd geannuleerd of wanneer de bestelling werd teruggezonden en je hebt de klant terugbetaald.<br>
                - Mail: “Je bestelling is terugbetaald.”</li>

            <li><strong>Mislukt</strong> = Dit kan je gebruiken als de betaling van de bestelling is mislukt.<br>
                - Mail: “Je betaling is mislukt.”</li>
        </ul>
    </details>



        <table>
            <thead>
                <tr>
                    <th>Bestelnummer</th>
                    <th>Product(en)</th>
                    <th>SKU</th>
                    <th>Datum</th>
                    <th>Status</th>
                    <th>Verzendgegevens</th>
                    <th>Totaalprijs</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($orders as $order) : ?>
                    <tr>
                        <td>#<?php echo $order->get_id(); ?></td>
                        <td>
                            <?php foreach ($order->get_items() as $item) : ?>
                                <?php echo esc_html($item->get_name()); ?> x<?php echo $item->get_quantity(); ?><br>
                            <?php endforeach; ?>
                        </td>
                        <td>
                            <?php foreach ($order->get_items() as $item) : ?>
                                <?php echo esc_html($item->get_product()->get_sku()); ?><br>
                            <?php endforeach; ?>
                        </td>
                        <td><?php echo $order->get_date_created()->date('d-m-Y H:i'); ?></td>
                        <td><?php echo wc_get_order_status_name($order->get_status()); ?></td>
                        <td>
                            <?php $shipping = $order->get_address('shipping'); ?>
                            <?php echo esc_html($shipping['first_name'] . ' ' . $shipping['last_name']); ?><br>
                            <?php echo esc_html($shipping['address_1']); ?><br>
                            <?php echo esc_html($shipping['postcode'] . ' ' . $shipping['city']); ?><br>
                            <?php echo esc_html($shipping['country']); ?>
                        </td>
                        <td>€<?php echo number_format($order->get_total(), 2, ',', '.'); ?></td>
                        <td class="order-actions">
                            <form method="post">
                                <input type="hidden" name="order_id" value="<?php echo $order->get_id(); ?>">
                                <select name="order_status" class="input-field">
                                    <?php foreach (wc_get_order_statuses() as $status => $label) : ?>
                                        <option value="<?php echo esc_attr($status); ?>" <?php selected($order->get_status(), $status); ?>>
                                            <?php echo esc_html($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <button type="submit" class="btn">Bijwerken</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="text-center" style="margin-top: 30px;">
            <a href="<?php echo home_url(); ?>" class="btn">Terug naar Home</a>
            <a href="/claire" class="btn">Terug naar Claire</a>
        </div>
    </div>
</body>
</html>
