<?php
$pageTitle = 'Cart list';
include('includes/header.php');
?>
    <style>
        :root {
            --bg-dark: #191919;
            --accent-cyan: #00d4ff;
            --text-light: #f5f5f5;
            --card-bg: #262626;
            --border-color: rgba(0, 212, 255, 0.3);
        }
.card_list_data {
    margin-top:150px;
    padding: 0 50px;
}

        .page-title {
            font-size: 2.5rem;
            color: var(--accent-cyan);
            text-align: center;
            margin-bottom: 2rem;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .fleeeccape_cart_container {
            display: grid;
            grid-template-columns: 3fr 1fr;
            gap: 2rem;
            margin-bottom: 2rem;
        }

        .fleeeccape_cart_table_wrapper {
            width: 100%;
        }

        .fleeeccape_cart_summary_wrapper {
            width: 100%;
        }

        .cart-list {
            background-color: var(--card-bg);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            overflow: hidden;
        }

        .cart-table {
            width: 100%;
            border-collapse: collapse;
        }

        .cart-table thead {
            background-color: #1a1a1a;
        }

       .cart-table th {
    padding: 13px 10px;
    text-align: left;
    color: var(--accent-cyan);
    font-weight: bold;
    text-transform: uppercase;
    font-size: 0.9rem;
    letter-spacing: 1px;
    border-bottom: 2px solid var(--border-color);
    color: #cdcdcd;
}

        .cart-table th:last-child {
            text-align: center;
        }

        .cart-table tbody tr {
            border-bottom: 1px solid var(--border-color);
            transition: background-color 0.3s ease;
        }

        .cart-table tbody tr:hover {
            background-color: rgba(0, 212, 255, 0.05);
        }

        .cart-table tbody tr:last-child {
            border-bottom: none;
        }

        .cart-table td {
    padding: 11px 9px;
    vertical-align: middle;
}

        .fleeeccape_cart_image {
            width: 70px;
            height: 60px;
            object-fit: cover;
            display: block;
            border-radius: 8px;
        }

        .item-details {
            
        }

        .item-title {
            font-size: 14px;
            color: var(--accent-cyan);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            font-weight: bold;
        }

        .item-info {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            font-size: 0.85rem;
            color: #aaa;
        }

        .item-info span {
            display: flex;
            align-items: center;
            gap: 0.3rem;
        }

        .item-price {
            font-size: 14px;
            color: var(--accent-cyan);
            font-weight: bold;
        }

        .item-actions {
            display: flex;
            flex-direction: column;
            gap: 0.8rem;
            align-items: center;
        }

      .btn-remove {
    background-color: #ff4444;
    color: white;
    border: none;
    padding: 3px 13px;
    border-radius: 4px;
    cursor: pointer;
    font-weight: bold;
    font-size: 0.95rem;
    transition: all 0.3s ease;
}

        .btn-remove:hover {
            background-color: #cc0000;
            transform: scale(1.05);
        }

        .quantity-control {
            display: flex;
            align-items: center;
            gap: 0.8rem;
            background-color: #1a1a1a;
            padding: 0.5rem 1rem;
            border-radius: 8px;
            border: 1px solid var(--border-color);
        }

        .quantity-btn {
            background-color: var(--accent-cyan);
            color: var(--bg-dark);
            border: none;
            width: 30px;
            height: 30px;
            border-radius: 5px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1.2rem;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .quantity-btn:hover {
            background-color: #00b8e6;
            transform: scale(1.1);
        }

        .quantity-value {
            font-size: 1.1rem;
            font-weight: bold;
            color: var(--accent-cyan);
            min-width: 30px;
            text-align: center;
        }

        .cart-summary {
    background-color: var(--card-bg);
    border: 2px solid var(--border-color);
    border-radius: 12px;
    padding: 10px;
    margin-top: 0;
}

        .summary-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 1rem 0;
            border-bottom: 1px solid var(--border-color);
        }

        .summary-row:last-child {
            border-bottom: none;
            padding-top: 1.5rem;
            margin-top: 0.5rem;
        }

        .summary-label {
            font-size: 1.1rem;
            color: #ccc;
        }

        .summary-value {
            font-size: 1.2rem;
            color: var(--text-light);
            font-weight: bold;
        }

        .summary-total .summary-label {
            font-size: 1.5rem;
            color: var(--accent-cyan);
            font-weight: bold;
        }

        .summary-total .summary-value {
            font-size: 1.8rem;
            color: var(--accent-cyan);
        }

     

        .empty-cart {
            text-align: center;
            padding: 4rem 2rem;
            color: #999;
        }

        .empty-cart-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
        }

        .empty-cart-text {
            font-size: 1.3rem;
        }

        @media (max-width: 768px) {
            .fleeeccape_cart_container {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }

            .cart-table {
                font-size: 0.85rem;
            }

            .cart-table th,
            .cart-table td {
                padding: 0.8rem 0.5rem;
            }

            .item-image {
                width: 60px;
                height: 60px;
                font-size: 1.5rem;
            }

            .item-title {
                font-size: 0.9rem;
            }

            .item-info {
                flex-direction: column;
                gap: 0.3rem;
            }

            .item-price {
                font-size: 1.1rem;
            }

            .quantity-control {
                padding: 0.4rem 0.6rem;
            }

            .btn-remove {
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }
        }
        .check_out_cart_bnt.bg_bnt_custom {
    font-size: 15px;
    width: auto;
    margin: 0 auto;
    padding: 4px 12px;
    border-radius: 4px;
    margin-top: 14px;
    margin-bottom: 3px;
}
.cartlist_checkout_bnt_div {
    text-align: center;
}
    </style>

    <div class="card_list_data">
        <h1 class="page-title">🛒 Your Cart</h1>

        <!-- Cart Layout with Table and Summary -->
        <div class="fleeeccape_cart_container">
            <!-- Left Side: Cart Table (col-6) -->
            <div class="fleeeccape_cart_table_wrapper">
                <div class="cart-list">
                    <table class="cart-table">
                        <thead>
                            <tr>
                                <th>Image</th>
                                <th>Game Details</th>
                                <th>Price</th>
                                <th>Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <img src="https://picsum.photos/100/100?random=1" alt="The Lift" class="fleeeccape_cart_image">
                                </td>
                                <td>
                                    <div class="item-details">
                                        <div class="item-title">THE LIFT (PRIVATE GAME ONLY)</div>
                                        <div class="item-info">
                                            <span>⏱️ 60 MINUTES</span>
                                            <span>👥 2-4 GUESTS</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="item-price">$40/GUEST</div>
                                </td>
                                <td>
                                    <div class="quantity-control">
                                        <button class="quantity-btn">-</button>
                                        <span class="quantity-value">1</span>
                                        <button class="quantity-btn">+</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="item-actions">
                                        <button class="btn-remove"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <img src="https://picsum.photos/100/100?random=2" alt="Ice Walker" class="fleeeccape_cart_image">
                                </td>
                                <td>
                                    <div class="item-details">
                                        <div class="item-title">ICE WALKER - GOT (PRIVATE GAME ONLY)</div>
                                        <div class="item-info">
                                            <span>⏱️ 60 MINUTES</span>
                                            <span>👥 2-8 GUESTS</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="item-price">$45/GUEST</div>
                                </td>
                                <td>
                                    <div class="quantity-control">
                                        <button class="quantity-btn">-</button>
                                        <span class="quantity-value">2</span>
                                        <button class="quantity-btn">+</button>
                                    </div>
                                </td>
                                <td>
                                    <div class="item-actions">
                                        <button class="btn-remove"><i class="fa fa-trash" aria-hidden="true"></i></button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Right Side: Cart Summary (col-6) -->
            <div class="fleeeccape_cart_summary_wrapper">
                <div class="cart-summary">
            <div class="summary-row">
                <span class="summary-label">Total Items:</span>
                <span class="summary-value">2 Games</span>
            </div>
            <div class="summary-row">
                <span class="summary-label">Subtotal:</span>
                <span class="summary-value">$130</span>
            </div>
            <div class="summary-row summary-total">
                <span class="summary-label">Total:</span>
                <span class="summary-value">$130</span>
            </div>
            <div class="cartlist_checkout_bnt_div">
                <button class="check_out_cart_bnt bg_bnt_custom ">Proceed to Checkout</button>
            </div>
        </div>
            </div>
        </div>
    </div>

<?php include('includes/footer.php'); ?>