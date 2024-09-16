<div class="container my-5">
    <h1 class="mb-4">Operatoren Übersicht</h1>

    <div class="alert alert-info">
        <strong>Anzahl verschiedener Operatoren:</strong> <span class="fw-bold"><?php echo $distinct_operators_count; ?></span>
    </div>

    <h5 class="mt-4">Einträge pro Operator:</h5>
    <table class="table table-striped table-bordered table-hover">
        <thead class="table-dark">
            <tr>
                <th scope="col" class="text-center">Platz</th>
                <th scope="col" class="text-center">Operator</th>
                <th scope="col" class="text-center">Anzahl der QSO's</th>
            </tr>
        </thead>
        <tbody>
            <?php $i=1;
                foreach ($operator_entries as $entry): ?>
                <tr>
                    <td class="text-center"><?php echo $i; ?></td>
                    <td class="text-center">
                        <?php 
                        if ($i == 1) {
                            echo '<i class="fas fa-trophy" style="color: #e6d500;"></i> ';
                        } elseif ($i == 2) {
                            echo '<i class="fas fa-trophy" style="color: #efefef;"></i> ';
                        } elseif ($i == 3) {
                            echo '<i class="fas fa-trophy" style="color: #c26709;"></i> ';
                        }
                        ?>
                        <?php echo htmlspecialchars($entry->COL_OPERATOR); ?> <a target="_blank" href="https://www.qrz.com/db/<?php echo strtoupper($entry->COL_OPERATOR); ?>"><img style="vertical-align: baseline" width="16" height="16" src="<?php echo base_url(); ?>images/icons/qrz.png" alt="Lookup <?php echo strtoupper($entry->COL_OPERATOR); ?> on QRZ.com"></a></td>
                    <td class="text-center"><?php echo htmlspecialchars($entry->count_per_operator); ?></td>
                </tr>
            <?php 
            $i++;
            endforeach; ?>
        </tbody>
    </table>
</div>