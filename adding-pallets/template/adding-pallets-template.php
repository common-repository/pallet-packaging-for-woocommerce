<?php

namespace EnPpfwDropshipTemplate;

use EnPpfwPallethouse\EnPpfwPallethouse;

if (!class_exists('EnPpfwDropshipTemplate')) {

    class EnPpfwDropshipTemplate
    {

        /**
         * Convert array to string for table using
         * @param string $index
         * @param array $data
         * @return string
         */
        static public function en_arrange_table_data($index, $data)
        {
            return "<$index> " . implode(" <$index> ", $data) . " </$index>";
        }

        /**
         * Pallethouse template
         * @return false|string
         */
        static public function en_load()
        {
            $en_heading = $en_data = [];
            $en_pship_list = EnPpfwPallethouse::get_data(['enp' => 'pship']);

            extract(\EnEnp::en_enp_filter_data('pship'));

            ksort($en_heading);
            ksort($en_data);

            ob_start();
            ?> <!-- Close PHP-->

            <div class="en_enp_pship_main_div">
            <button onclick="en_show_popup_enp(event)"
                    class="en-adding-pallets-btn button-primary"><?php _e('Add Pallet', 'eniture-technology'); ?></button>
            <div class="en_enp_success_message">
                <strong><?php _e('Success!', 'eniture-technology'); ?> </strong><span></span></div>

            <table class="en_enp_table en_enp_pship_table">
            <thead>
            <tr>
                <?php echo \EnEnp::en_arrange_table_data('th', $en_heading); ?>
                <th><?php _e('Available', 'eniture-technology'); ?></th>
                <th><?php _e('Action', 'eniture-technology'); ?></th>
            </tr>
            </thead>

            <?php
            // Start PHP

            echo \EnEnp::en_arrange_enp_table_row($en_pship_list, $en_data, 0);

            echo '</table>';

            echo '</div>';

            return ob_get_clean();
        }

    }

}
