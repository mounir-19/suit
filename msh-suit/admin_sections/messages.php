<?php
// Get all contact messages
$messages_query = "SELECT * FROM contact_messages ORDER BY created_at DESC";
$messages_result = $mysqli->query($messages_query);
$messages = $messages_result->fetch_all(MYSQLI_ASSOC);
?>

<div class="row mb-4">
    <div class="col-12">
        <h2><i class="fas fa-envelope"></i> Contact Messages</h2>
        <p class="text-muted">View and manage customer contact messages.</p>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5><i class="fas fa-list"></i> All Messages</h5>
                <span class="badge bg-primary"><?= count($messages) ?> Total Messages</span>
            </div>
            <div class="card-body">
                <?php if (empty($messages)): ?>
                    <p class="text-muted">No messages found.</p>
                <?php else: ?>
                    <div class="accordion" id="messagesAccordion">
                        <?php foreach ($messages as $index => $message): ?>
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading<?= $index ?>">
                                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapse<?= $index ?>">
                                        <div class="d-flex justify-content-between w-100 me-3">
                                            <div>
                                                <strong><?= htmlspecialchars($message['name']) ?></strong>
                                                <small class="text-muted ms-2"><?= htmlspecialchars($message['email']) ?></small>
                                            </div>
                                            <div>
                                                <small class="text-muted"><?= date('M j, Y g:i A', strtotime($message['created_at'])) ?></small>
                                            </div>
                                        </div>
                                    </button>
                                </h2>
                                <div id="collapse<?= $index ?>" class="accordion-collapse collapse" data-bs-parent="#messagesAccordion">
                                    <div class="accordion-body">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h6>Message:</h6>
                                                <p><?= nl2br(htmlspecialchars($message['message'])) ?></p>
                                                
                                                <h6>Contact Information:</h6>
                                                <p>
                                                    <strong>Email:</strong> <?= htmlspecialchars($message['email']) ?><br>
                                                    <strong>Phone:</strong> <?= htmlspecialchars($message['phone']) ?>
                                                </p>
                                            </div>
                                            <div class="col-md-4 text-end">
                                                <button class="btn btn-danger btn-sm" onclick="deleteItem('message', <?= $message['id'] ?>)">
                                                    <i class="fas fa-trash"></i> Delete Message
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
