<?php

namespace App\Models;

use Core\Model;
use PDO;

class StatisticsModel extends Model
{
    public function getEventByNextMonth()
    {
        $sql = "SELECT to_char(date_trunc('month', start_at), 'Month') as month, count(*) as nb_event FROM EVENT";
        $sql .= " WHERE date_trunc('month', start_at) > (date_trunc('month', current_date - interval '1' month))";
        $sql .= " GROUP BY month ORDER BY month ASC";

        $query = $this->database->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }

    // "SELECT to_char(date_trunc('month', start_at), 'Month') as month, count(*) as nb_event FROM EVENT 
    // WHERE date_trunc('month', start_at) > (date_trunc('month', current_date - interval '1' month))
    // GROUP BY month ORDER BY month ASC;"

    /**
     * Get new customer by day
     */
    public function getNewCustomerByDay()
    {
        $sql = "SELECT to_char(date_trunc('d', created_at), 'DDD') as day, count(*) as nb_customer FROM CUSTOMER";
        $sql .= " GROUP BY day ORDER BY day ASC";

        $query = $this->database->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    // 
    // 

    /**
     * get incomming money from orders by day
     */
    public function getIncommingMoneyFromOrdersByDay()
    {
        $sql = "SELECT to_char(orders.created_at, 'DDD') as day, sum(order_line.price)";
        $sql .= " FROM orders";
        $sql .= " LEFT JOIN order_line ON order_line.order_id = orders.id";
        $sql .= " GROUP BY day";

        $query = $this->database->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
    // SELECT to_char(orders.created_at, 'DDD') as day, sum(order_line.price)
    // FROM orders
    // LEFT JOIN order_line ON order_line.order_id = orders.id
    // GROUP BY day

    /**
     * Get usage products
     */
    public function getUsageProducts()
    {
        $sql = "SELECT order_line.text, count(*)";
        $sql .= " FROM order_line";
        $sql .= " GROUP BY order_line.text";

        $query = $this->database->prepare($sql);
        $query->execute();
        return $query->fetchAll(PDO::FETCH_ASSOC);
    }
}
