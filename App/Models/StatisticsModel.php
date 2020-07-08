<?php

namespace App\Models;

use Core\Model;
use PDO;

class StatisticsModel extends Model
{
    public function getEventByNextMonth()
    {
        $sql = "SELECT 
        to_char(date_trunc('month', start_at), 'MM') AS month,
        count(*) as nb_event 
        FROM EVENT
        WHERE date_trunc('month', start_at) > (date_trunc('month', current_date - interval '1' month))
        GROUP BY month
        ORDER BY month ASC";

        $query = $this->database->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get new customer by day
     */
    public function getNewCustomerByDay()
    {
        $sql = "SELECT to_char(date_trunc('d', created_at), 'DD Month') as day, count(*) as nb_customer FROM CUSTOMER
        WHERE created_at > NOW() - INTERVAL '30 day'
        GROUP BY day ORDER BY day ASC";

        $query = $this->database->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * get incomming money from orders by day
     */
    public function getIncommingMoneyFromOrdersByDay()
    {
        $sql = "SELECT to_char(orders.created_at, 'DD Month') as day, sum(order_line.price)
        FROM orders
        LEFT JOIN order_line ON order_line.order_id = orders.id
        WHERE orders.created_at > NOW() - INTERVAL '30 day'
        GROUP BY day";

        $query = $this->database->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get usage products
     */
    public function getUsageProducts()
    {
        $sql = "SELECT order_line.text, count(*)";
        $sql .= " FROM order_line";
        $sql .= " GROUP BY order_line.text";
        $sql .= " LIMIT 100";

        $query = $this->database->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // Franchise

    /**
     * Get number order by month for user
     */
    public function getCountOrderForUserByMonth($user_id)
    {
        $sql = "SELECT to_char(date_trunc('month', created_at), 'Month') AS Month, count(*) as count
        FROM orders WHERE user_id = :user_id
        GROUP BY Month";

        $query = $this->database->prepare($sql);
        $query->execute([':user_id' => $user_id]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // chiffre d'affaire TTC
    public function getIncommingMoneyFromOrdersByMonthForUser($user_id)
    {
        $sql = "SELECT to_char(orders.created_at, 'Month') AS Month, sum(order_line.price) AS sum
        FROM orders
        LEFT JOIN order_line ON orders.id = order_line.order_id
        WHERE orders.user_id = :user_id
        GROUP BY Month";

        $query = $this->database->prepare($sql);
        $query->execute([':user_id' => $user_id]);
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
